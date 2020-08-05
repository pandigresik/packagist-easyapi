# easyAPI

## What is easyAPI?

easyAPI is skeleton REST API application and integrated with swagger to generate documentation API for Codeigniter 4.
With easyAPI you can generate REST API less than 5 minutes.

## Installation & setup

- composer require asligresik/easyapi 
## Publish initial file for swagger documentation
- `php spark api:publish` this command will copy file 
    - src/Controllers/Swagger.php to app/Controllers
    - src/Views to app/Views
    - src/public/assets to public/assets
## Generate REST API
- `php spark api:generate`
after that, system will ask you table name will generate that REST API. We can choose one table or all, if we want generate all write `all` or write one table name exist in your database
If there is no error, system will generate for you controller, model and entity file.
- Last you must add new route will display in last command to `app/Config/Routes.php`.
- Generate api.yaml using command `./vendor/bin/openapi -o ./public/assets/api.yaml ./app` to show API docs using swagger using datasource format yaml (default)
- Generate api.json using command `./vendor/bin/openapi -o ./public/assets/api.json ./app` to show API docs using swagger using datasource format json (optional)
- Open API documentation in http://localhost:8080/swagger



## Todo
- [X] create example using this application

## Copyright

easyApi dikembangkan dan dimaintain oleh [asligresik](https://github.com/pandigresik)

## Lisensi

Lisensi dari easyApi adalah [MIT License](LICENSE) namun proyek yang dibangun menyeseuaikan dengan kebijakan masing-masing