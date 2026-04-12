## Para trabajar con EXEL es necesaria la lib: maatwebsite/excel
```shell
composer require maatwebsite/excel
```
Para poder utilizarla es necesario modificar el init .php modificando des comentando las siguientes líneas:

```text
extension=openssl
extension=fileinfo
extension=gd
extension=zip
```
Para detectar la ubicación del init.php se utiliza el comando 
```shell
php --ini
```
