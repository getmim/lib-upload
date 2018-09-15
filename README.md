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
module atau aplikasi untuk validasi file upload:

```php
return [
    // ...
    'libUpload' => [
        'base' => [
            'local' => 'media' // folder dimana file upload disimpan
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
                ]
            ]
        ]
    ]
    // ...
];
```

## Custom Keeper

Selain menyimpan file di folder `./media`, developer bisa juga membuatkan
metode penyimpanan lain seperti ftp atau aws. Untuk itu, buatkan class
yang mengimplementasikan interface `LibUpload\Iface\Keeper` yang memiliki
static method sebagai berikut:

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

### lastError(): ?string

Mengambil informasi error terakhir.

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
sebagai berikuta:

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
    "error": 0,
    "message": "OK",
    "data": {
        "path": "fi/le/lo/ca/lid.jpg"
    }
}
```

## Validator

Jika module `lib-validator` terpasang, maka module ini mendaftarkan satu tipe validasi dengan
nama `upload`. Tipe validasi ini memastikan nilai file yang dikirim pernah diupload.

```php
return [
    'upload' => true,
    'upload' => 'avatar-file'
];
```

Jika nilai yang diberikan adalah `true`, maka validor hanya mengecek jika file pernah diupload
dan ada di server. Atau bisa juga memberikan nilai string nama form upload untuk memastikan file
yang dikirim cocok dengan file yang terupload.