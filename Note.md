Note
===========
### Post System
post hay two type:
1. Draft (automaticaly created when user open new post page)
    ```php
    Status Code : 1
    ```
2. Published
    ```php
    Status Code : 2
    ```
Post also has 2 extra type for helper
1. Autosave (automaticaly created when user open new post page, and update at 1min interval)
    ```php
    Status Code : 0
    ```
2. Revisions (ceate maximum 3 revision, and commit each post saved)
    ```php
    Status Code : 3
    ```
Post has 2 type of deletion
1. Soft Delete (Post going to trash category so it can be restore)
    ```php
    Status Code : 1
    ```
2. Permanently Deleted (Deleted Permanently from database)

### PPDB System
Regnumber taxonomy :
    ```php
    AAAAAAAAAABBBB
    ```
    A. time(number unix timestamp)
    B. Last 4 digit NISN (number)

Reguler taxonomy :
    1. nilai UN lengkap ijazah ( divalidasi )
    a.

Prestasi taxonomy :
    1. nilai rapor (validasi upload rapor)
    2. piagam (validasi upload piagam)
    a. sort dengan minimum nilai etc 75
    b. konfirmasi penerimaan


todo
```todo
    1. update tabel psb_value tambah field validasi, konfirmasi
    2. update psb_value data json format to add gambar rapor
```

Validasi -> form wizard style


089-892-74496
