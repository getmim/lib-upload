<?php
/**
 * UploadController
 * @package lib-upload
 * @version 0.0.1
 */

namespace LibUpload\Controller;

use LibForm\Library\Form;
use LibUpload\Model\Media;
use LibUpload\Model\MediaAuth as MAuth;
use LibValidator\Library\Validator;

class UploadController extends \Api\Controller
{
    private $user_id;
    private $object_id;
    private $object_type;

    private function isAuthorized(): bool{
        $this->user_id = $this->user->isLogin() ? $this->user->id : 0;

        $auths = $this->config->libUpload->authorizer;
        if($auths){
            foreach($auths as $name => $class){
                $obj_id = $class::getAuthId();
                if(!$obj_id)
                    continue;

                $this->object_id = $obj_id;
                $this->object_type = $name;
            }
        }

        return ($this->user_id || $this->object_id);
    }

    private function makeToken(string $form, object $file): string{
        $tmp_file = tempnam(sys_get_temp_dir(), '');
        $tmp_name = basename($tmp_file);

        $token_payload = [
            $this->req->getIP(),
            $this->req->agent,
            $form,
            $tmp_name
        ];

        $token_payload = implode('/', $token_payload);

        $tmp_name.= '/' . md5($token_payload);

        return $tmp_name;
    }

    private function validateToken(string $form, string $token): bool{
        $tokens = explode('/', $token);
        $token_payload = $tokens[1] ?? null;
        if(!$token_payload)
            return false;

        $match_payload = [
            $this->req->getIP(),
            $this->req->agent,
            $form,
            $tokens[0]
        ];

        $match_payload = md5(implode('/', $match_payload));

        return $match_payload == $token_payload;
    }

    public function chunkAction(){
        if(!$this->isAuthorized())
            return $this->resp(401);

        $form = new Form('lib-upload-chunk');
        if(!($valid = $form->validate()))
            return $this->resp(422, $form->getErrors());

        if(!$this->validateToken($valid->form, $valid->token))
            $this->resp(400, 'Invalid token value');

        $tokens = explode('/', $valid->token);
        $tmp_name = $tokens[0];

        $tmp_file = rtrim(sys_get_temp_dir(), '/') . '/mim-upload-' . $tmp_name;

        $file = $valid->file;
        $file_content = file_get_contents($file['tmp_name']);

        $f = fopen($tmp_file, 'a');
        fwrite($f, $file_content);
        fclose($f);

        $this->resp(0, ['size' => filesize($tmp_file)]);
    }

    public function filterAction(){
        if(!$this->isAuthorized())
            return $this->resp(401);

        $cond = [];

        if($this->config->libUpload->filter->own && $this->user->isLogin())
            $cond['user'] = $this->user->id;

        if(!is_null($hash = $this->req->getQuery('hash')))
            $cond['identity'] = $hash;

        if(!is_null($mime = $this->req->getQuery('type'))){
            if(false === strstr($mime, '*')){
                $cond['mime'] = explode(',', $mime);
            }else{
                $mimes = explode('/', $mime);
                $left  = false;
                $right = false;

                if(count($mimes) === 2){
                    if($mimes[0] === '*')
                        $left = true;
                    if($mimes[1] === '*')
                        $right = true;

                    if($left && $right)
                        $position = 'both';
                    elseif($left)
                        $position = 'left';
                    elseif($right)
                        $position = 'right';

                    $mime = str_replace('*', '', $mime);
                    $cond['mime'] = ['__like', $mime, $position];
                }
            }
        }

        if(!is_null($name = $this->req->getQuery('query') ?? $this->req->getQuery('name')))
            $cond['original'] = ['__like', $name];

        $result = [];

        $media = Media::get($cond, 20, 1);
        if($media){
            foreach($media as $medium){
                $file_urls = json_decode($medium->urls);
                $result[] = [
                    'id'   => (int)$medium->id,
                    'url'  => $file_urls[0] ?? NULL,
                    'path' => $medium->path,
                    'name' => $medium->original,
                    'type' => $medium->mime,
                    'size' => (int)$medium->size
                ];
            }
        }

        $this->resp(0, $result);
    }

    public function finalizeAction(){
        if(!$this->isAuthorized())
            return $this->resp(401);

        $form = new Form('lib-upload-finalize');
        if(!($valid = $form->validate()))
            return $this->resp(422, $form->getErrors());

        if(!$this->validateToken($valid->form, $valid->token))
            return $this->resp(400, 'Invalid token value');

        $tokens = explode('/', $valid->token);
        $tmp_name = $tokens[0];

        $tmp_file = rtrim(sys_get_temp_dir(), '/') . '/mim-upload-' . $tmp_name;

        if(!is_file($tmp_file))
            return $this->resp(400, 'Complete file not uploaded');

        // validate the file agains the form
        $rule = [
            'file' => [
                'rules' => [
                    'required' => true,
                    'upload-file' => TRUE
                ]
            ],
            'form' => [
                'rules' => [
                    'required' => true 
                ]
            ]
        ];
        $result = (object)[
            'file' => [
                'error'    => 0,
                'size'     => filesize($tmp_file),
                'type'     => mime_content_type($tmp_file),
                'name'     => $valid->name,
                'tmp_name' => $tmp_file
            ],
            'form' => $valid->form
        ];
        // fix mp3 mime
        if ($result->file['type'] == 'application/octet-stream') {
            if (preg_match('!mp3$!', $result->file['name'])) {
                $result->file['type'] = 'audio/mpeg';
            }
        }

        list($res, $errs) = Validator::validate(objectify($rule), $result);
        if($errs)
            return $this->resp(422, $errs);

        $file_md5 = md5_file($result->file['tmp_name']);
        $up_form = (array)($this->config->libUpload->forms->{$result->form}->keeper ?? []);

        $handlers = $this->config->libUpload->keeper->handlers;
        if($up_form){
            $used_handlers = [];
            foreach($handlers as $keeper => $opt){
                if(in_array($keeper, $up_form))
                    $used_handlers[$keeper] = $opt;
            }
            $handlers = $used_handlers;
        }

        // make sure the file is not yet uploaded
        $media = Media::getOne(['identity'=>$file_md5]);
        $file_urls = [];
        if(!$media){
            $image_width = 0;
            $image_height= 0;

            $file = (object)[
                'name'   => $result->file['name'],
                'type'   => $result->file['type'],
                'size'   => $result->file['size'],
                'source' => $result->file['tmp_name'],
                'target' => null
            ];

            if(fnmatch('image/*', $file->type))
                list($image_width, $image_height) = getimagesize($file->source);

            $target = substr($file_md5, 0, 2) . '/'
                    . substr($file_md5, 2, 2) . '/'
                    . substr($file_md5, 4, 2) . '/'
                    . substr($file_md5, 6, 2) . '/';

            $exts = explode('.', $file->name);
            $ext  = end($exts);
            $ext  = strtolower($ext);

            $target_name = $file_md5 . '.' . $ext;

            $target.= $target_name;

            $file->target = $target;

            $error = false;
            
            foreach($handlers as $keeper => $opt){
                if(!$up_form && !$opt->use)
                    continue;
                
                $class = $opt->class;
                if(!($file_url = $class::save($file))){
                    $error = $class::lastError();
                    break;
                }

                $file_urls[] = $file_url;
            }

            if($error)
                return $this->resp(500, null, $error);

            if(!$file_urls)
                return $this->resp(500, null, 'No file keeper used to save the file');

            // now insert it to db
            $media = [
                'name'      => $target_name,
                'original'  => $file->name,
                'mime'      => $file->type,
                'user'      => $this->user_id,
                'path'      => $target,
                'form'      => $result->form,
                'size'      => $file->size,
                'identity'  => $file_md5,
                'urls'      => json_encode($file_urls)
            ];

            if($image_height)
                $media['height'] = $image_height;
            if($image_width)
                $media['width'] = $image_width;

            if(!$id = Media::create($media))
                return $this->resp(500, Media::lastError());

            if($this->object_id){
                MAuth::create([
                    'media'  => $id,
                    'type'   => $this->object_type,
                    'object' => $this->object_id
                ]);
            }

            $media = Media::getOne(['id'=>$id]);
        }

        if(is_file($tmp_file))
            unlink($tmp_file);

        $file_urls = json_decode($media->urls);
        return $this->resp(0, [
            'id'   => (int)$id,
            'url'  => $file_urls[0] ?? NULL,
            'path' => $media->path,
            'name' => $media->original,
            'type' => $media->mime,
            'size' => (int)$media->size
        ]);
    }

    public function initAction() {
        if(!$this->isAuthorized())
            return $this->resp(401);

        $form = new Form('lib-upload');

        if(!$form->validate())
            return $this->resp(422, $form->getErrors());

        $result  = $form->getResult();
        $up_form = (array)($this->config->libUpload->forms->{$result->form}->keeper ?? []);

        $file_md5 = md5_file($result->file['tmp_name']);

        $handlers = $this->config->libUpload->keeper->handlers;
        if($up_form){
            $used_handlers = [];
            foreach($handlers as $keeper => $opt){
                if(in_array($keeper, $up_form))
                    $used_handlers[$keeper] = $opt;
            }
            $handlers = $used_handlers;
        }

        // make sure the file is not yet uploaded
        $media = Media::getOne(['identity'=>$file_md5]);
        $file_urls = [];
        if(!$media){
            $image_width = 0;
            $image_height= 0;

            $file = (object)[
                'name'   => $result->file['name'],
                'type'   => $result->file['type'],
                'size'   => $result->file['size'],
                'source' => $result->file['tmp_name'],
                'target' => null
            ];

            if(fnmatch('image/*', $file->type))
                list($image_width, $image_height) = getimagesize($file->source);

            $target = substr($file_md5, 0, 2) . '/'
                    . substr($file_md5, 2, 2) . '/'
                    . substr($file_md5, 4, 2) . '/'
                    . substr($file_md5, 6, 2) . '/';

            $exts = explode('.', $file->name);
            $ext  = end($exts);
            $ext  = strtolower($ext);

            $target_name = $file_md5 . '.' . $ext;

            $target.= $target_name;

            $file->target = $target;

            $error = false;
            
            foreach($handlers as $keeper => $opt){
                if(!$up_form && !$opt->use)
                    continue;

                $class = $opt->class;
                if(!($file_url = $class::save($file))){
                    $error = $class::lastError();
                    break;
                }

                $file_urls[] = $file_url;
            }

            if($error)
                return $this->resp(500, null, $error);

            if(!$file_urls)
                return $this->resp(500, null, 'No file keeper used to save the file');

            // now insert it to db
            $media = [
                'name'      => $target_name,
                'original'  => $file->name,
                'mime'      => $file->type,
                'user'      => $this->user_id,
                'path'      => $target,
                'form'      => $result->form,
                'size'      => $file->size,
                'identity'  => $file_md5,
                'urls'      => json_encode($file_urls)
            ];

            if($image_height)
                $media['height'] = $image_height;
            if($image_width)
                $media['width'] = $image_width;

            if(!$id = Media::create($media))
                return $this->resp(500, Media::lastError());

            if($this->object_id){
                MAuth::create([
                    'media'  => $id,
                    'type'   => $this->object_type,
                    'object' => $this->object_id
                ]);
            }

            $media = Media::getOne(['id'=>$id]);
        }

        $file_urls = json_decode($media->urls);
        return $this->resp(0, [
            'id'   => (int)$media->id,
            'url'  => $file_urls[0] ?? NULL,
            'path' => $media->path,
            'name' => $media->original,
            'type' => $media->mime,
            'size' => (int)$media->size
        ]);
    }

    public function validateAction(){
        if(!$this->isAuthorized())
            return $this->resp(401);

        $form = new Form('lib-upload-validate');

        if(!($valid = $form->validate()))
            return $this->resp(422, $form->getErrors());

        // make sure upload handler is exists for this request
        $file_form = $valid->form;

        $up_form = (array)($this->config->libUpload->forms->{$file_form}->keeper ?? []);

        $handlers = $this->config->libUpload->keeper->handlers;
        if($up_form){
            $used_handlers = [];
            foreach($handlers as $keeper => $opt){
                if(in_array($keeper, $up_form))
                    $used_handlers[$keeper] = $opt;
            }
            $handlers = $used_handlers;
        }

        $up_exists = !!$handlers;
        if(!$up_form){
            foreach($handlers as $keeper => $opt){
                if(!$opt->use)
                    continue;

                $up_exists = true;
                break;
            }
        }

        if(!$up_exists)
            return $this->resp(500, null, 'No file keeper usable to save the file');

        $token = $this->makeToken($valid->form, $valid->file);

        $this->resp(0, ['token'=>$token]);
    }
}
