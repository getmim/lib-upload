# lib-upload

Adalah module yang bertugas menangani file upload. Secara default
semua file upload disimpan di folder `media`. Ada juga opsi untuk
menyimpan file upload tersebut di ftp atau aws. Sebagai catatan, 
ketika md5 suatu file sudah pernah ada di server, maka proses upload
akan di batalkan, dan akan mengembalikan informasi file yang sudah
pernah di upload sebelumnya.

## Instalasi

Jalankan perintah di bawah di folder aplikasi:

```
mim app install lib-upload
```

## Konfigurasi

Silahkan tambahkan konfigurasi seperti di bawah pada konfigurasi
module atau aplikasi:

```php
return [
    // ...
    'libUpload' => [
        'filter' => [
            // find file hanya dari file yang diupload oleh 
            // user bersangkutan
            'own' => false
        ],
        'base' => [
            'local' => 'media', // folder dimana file upload disimpan
            'host'  => 'http://site.mim/media/'
        ],
        'forms' => [
            'my-upload' => [
                'size' => [ // in byte
                    'min' => 0,
                    'max' => 233112
                ],
                'mime' => [
                    '*/*', // accept all
                    'image/*', // accept all image
                    'text/plain' // accept mime type text/plain
                ],
                'exts' => [
                    '*',
                    'jpg',
                    'txt'
                ],
                'image' => [ // for image file upload only
                    'width' => [ // in pixel
                        'min' => 10,
                        'max' => 1024
                    ],
                    'height' => [ // in pixel
                        'min' => 10,
                        'max' => 1024
                    ]
                ],
                // use only this keepr to store the file
                'keeper' => ['/name/']
            ]
        ]
    ]
    // ...
];
```
Sebagai catatan, module ini sudah mendaftarkan form upload dengan nama `std-image` untuk
menerima semua image, dan `std-audio` untuk menerima semua file audio.

## Custom Keeper

Selain menyimpan file di folder `./media`, developer bisa juga membuatkan
metode penyimpanan lain seperti ftp atau aws. Untuk itu, buatkan class
yang mengimplementasikan interface `LibUpload\Iface\Keeper` yang memiliki
static method sebagai berikut:

### getId(string $file): ?string

Fungsi yang akan dipanggil oleh system untuk mendapatkan id suatu file berdasarkan
full-url dari file tersebut. Fungsi ini diharapkan mengembalikan null jika keeper
tidak mengenali file, atau string file id jika diketahui.

### lastError(): ?string

Mengambil informasi error terakhir.

### save(object $file): bool

Method yang akan dipanggil ketika suatu file berhasil di upload dan sudah melewati
proses verifikasi rules. Method ini adalah static method yang akan dipanggil dengan
parameter sebagai berikuta:

```php
$file = (object)[
    // original file name
    'name'      => 'filename.jpg',
    
    // file mime type
    'type'      => 'image/jpg',

    // file size
    'size'      => 21975,

    // soure file to upload
    'source'    => '/tmp/file-source',

    // target file where to put
    'target'    => 'aa/bb/cc/filename.jpg'
];
```

Fungsi ini diharapkan mengembalikan nilai `true` jika berhasil, dan `false` jika
gagal.

## Implementasi Keeper

Untuk implementasinya, silahkan mengacup pada file keeper `LibUpload\Keeper\Local`.

Tambahkan juga konfigurasi seperti di bawah pada konfigurasi module agar
module ini tahu keberadaan keeper tersebut:

```php
return [
    // ...
    'libUpload' => [
        'keeper' => [
            'handlers' => [
                'my-keeper' => [
                    'class' => 'LibMyKeeper\\Library\\MyKeeper',
                    'use' => true
                ]
            ]
        ]
    ]
    // ...
];
```

Ketika event upload terjadi, semua `handlers` yang terdaftar dengan 
properti `use` adalah `true` akan di panggil. Jadi jika ada 5 keeper
terdaftar dan semuanya adalah `use => true`, maka file upload user
tersebut akan disimpan oleh 5 keeper.

## FrontEnd

Ketika melakukan upload file dari front-end. Pastikan mengirim dua parameter
sebagai berikut:

1. `file` File object
3. `form` Form name

Dalam bentuk html, maka bentuk di atas adalah sebagai berikut:

```html
<form method="POST" enctype="multipart/form-data" action="<?= $this->router->to('apiUpload') ?>">
    <input type="file" name="file">
    <input type="hidden" name="form" value="my-form">
    <button>Upload</button>
</form>
```

Endpoint upload menggunakan route dengan nama `apiUpload` dan melalui gate `api`. Nilai
yang dikembalikan dari endpoint ini adalah sebagai berikut:

```json
{
   "error":0,
   "message":"OK",
   "data":{
      "url":"/media/d4/7d/e0/3e/d47de03e7b5b7040d11fc8bfa5203271.jpg",
      "path":"d4/7d/e0/3e/d47de03e7b5b7040d11fc8bfa5203271.jpg",
      "name":"Nature-Beach-Scenery-Wallpaper-HD.jpg",
      "type":"image/jpeg",
      "size":446517
   }
}
```

### Filter

Route `apiUploadFilter` atau url `/upload/filter` bisa digunakan untuk memfilter file yang pernah di upload
oleh semua user. Endpoint ini menerima query string:

1. `name` Filter berdasarkan nama file ketika di upload. Menerima nama parsial.
1. `type` Filter berdasarkan mime type file. Karaketer `*` dianggap sebagai cocok dengan semua (ex: `image/*` ).
1. `hash` Filter berdasarkan hash file ( md5 ).

### Validate

Sebelum malanjutkan proses upload file ke server, sebaiknya validasi file yang akan di upload untuk meminimalisir
kemungkinan error setelah file terupload. Panggil route `/upload/validate` dengan body seperti di bawah:

```json
{
    "form": "form-name",
    "file": {
        "size": 123123,
        "type": "image/jpeg",
        "name": "file.jpg",
        "width": 1223,
        "height": 123321
    }
}
```

Properti `width` dan `height` khusus untuk file tipe gambar.

Jika file yang akan diupload cocok dengan form, maka validator akan mengembalikan
nilai `token` seperti di bawah yang bisa digunakan untuk upload dengan metode chunk:

```json
{
    "error": 0,
    "message": "OK",
    "data": {
        "token": "UzHz6q/528879b833183b4158551768335784f3"
    }
}
```

## Validator

Jika module `lib-validator` terpasang, maka module ini mendaftarkan tipe validasi dengan
sebagai berikut:

### upload

Tipe validasi ini memastikan nilai file yang dikirim pernah diupload.

```php
return [
    'upload' => true,
    'upload' => 'avatar-file'
];
```

Jika nilai yang diberikan adalah `true`, maka validor hanya mengecek jika file pernah diupload
dan ada di server. Atau bisa juga memberikan nilai string nama form upload untuk memastikan file
yang dikirim cocok dengan file yang terupload.

### upload-list

Sama dengan tipe validasi `upload`, kecuali tipe ini menerima multiple data dalam bentuk array
dan akan mencocokan masing-masing file dengan form yang ditentukan.

```php
return [
    'upload-list' => true,
    'upload-list' => 'avatar-file'
];
```

Nilai yang dikirimkan bisa berbentuk `["path/to/file.ext"]` atau object dalam array dengan
key media adalah `url` seperti `[{"url":"path/to/file.ext"}]`.

## Formatter

Module ini menambah satu formatter dengan nama `std-cover` untuk memformat data string object
menjadi object cover dengan label. Nilai property dengan format ini harus berbentuk seperti di
bawah:

```json
{"url":"...","label":"..."}
```

Nilai seperti di atas akan diubah menjadi object media untuk properti URL, dan object text untuk
properti label.

## Form

Module ini juga menambahkan satu form yang bisa di-extends untuk menghasilkan 2 form field baru, yaitu
`cover-url` dan `cover-label`. Kedua properti ini kemudian bisa digunakan untuk mengambil object cover 
dengan label.

## Form Handler

Pada form `std-cover`, cover memiliki URL dan label yang disimpan dalam object json. Untuk mempermudah
proses conversi dari properti `cover` menjadi form fields, module ini menambahkan satu library dengan
nama `LibUpload\Library\Form`.

```php
use LibUpload\Library\Form as UForm;

$object = (object)[
    'id' => 1,
    'cover' => '{"url":"/path/to/img.jpg","label":"..."}'
];

// parse property `cover` menjadi `cover-url` dan `cover-label`
UForm::parse($object, 'cover');

// $form = new Form('form-name');
// if(!($valid = $form->validate($object)))
//      return $this->resp('event/edit', $params);

// menggabungkan properti `cover-url` dan `cover-label` ke dalam properti `cover`
UForm::combine($valid, 'cover');
```

## Chunk Upload

Library ini mendukung chunk upload untuk memungkinkan upload file dengan ukuran yang besar.
Step yang harus dilakukan untuk upload chunk file adalah sebagai berikut:

### Dapatkan Token

Validasi file dengan memanggil route `/upload/validate` seperti contoh di atas untuk mendapatkan
token chunk upload.

### Split File

Split file dengan masing-masing chunk berukuran cukup kecil tapi tidak terlalu kecil untuk di upload
per-bagian. Ukuran yang umumnya digunakan adalah 200kb.

### Upload Chunk

Upload chunk ke route `/upload/chunk` **secara berurutan** dari yang paling awal ke yang akhir
dengan body seperti di bawah:

```
file::File   : Chunk file
form::String : Upload form name
token::String: File upload chunk token
```

### Finalize

Begitu semua chunk berhasil di upload, panggil route `/upload/finalize` dengan body seperti
dibawah dalam format json untuk menyelesaikan proses upload.

```json
form::String  : Upload form name
token::String : File upload chunk token
name::String  : Original file name
```

Jika proses upload berhasil, maka fungsi ini akan mengembalikan nilai yang sama dengan proses upload
metode biasa.