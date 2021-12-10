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
  - src/Commands/API/template to app/Commands/API/template

## Override default template

- edit file in folder app/Commands/API/template

## Generate REST API

- `php spark api:generate` or `php spark api:generate -n Modules\API\` to generate file in spesific folder
  after that, system will ask you table name will generate that REST API. We can choose one table or all, if we want generate all write `all` or write one table name exist in your database
  If there is no error, system will generate for you controller, model and entity file.
- Last you must add new route will display in last command to `app/Config/Routes.php`.
- Generate api.yaml using command `./vendor/bin/openapi -o ./public/assets/api.yaml ./app` to show API docs using swagger using datasource format yaml (default)
- Generate api.json using command `./vendor/bin/openapi -o ./public/assets/api.json ./app` to show API docs using swagger using datasource format json (optional)
- Open API documentation in http://localhost:8080/swagger

## Feature

- Support searching data using multiple column
- Support searching date column
- Support pagination

## Example

- join with other table, you can look at [ArtikelKategoriModel.php](https://github.com/pandigresik/easyAPI/blob/master/app/Models/ArtikelKategoriModel.php)
- example parameter in swagger for order data _{"order":[{"id":"desc"},{"tgl_upload":"asc"}]}_
- example parameter in swagger for search data _{"search":[{"id_kategori":1}]}_
- example parameter in swagger for search range data _{"search":[{"tgl_upload":{"start":"2016-01-01", "end":"2020-01-01"} }]}_
- example parameter in swagger for search using like _{"search":[{"judul":"membangun%25"}]}_ or _{"search":[{"judul":"%25membangun%25"}]}_ use `%25` not `%` you can place `%25` on before, after and combination before and after as your keyword to search data
- search data based on column name _{base_url}/artikels?search[id_kategori]=1_
- order data based on column name _{base_url}/artikels?order[id]=desc&order[tgl_upload]=asc_
- search and order data based on column name _{base_url}/artikels?search[id_kategori]=1&order[id]=desc&order[tgl_upload]=asc_
- search with pagination _{base_url}/artikels?page=1&limit=10_
- search and order with pagination _{base_url}/artikels?search[id_kategori]=1&order[id]=desc&order[tgl_upload]=asc&page=1&limit=10_
- search using between operator if we want filter data based range of date or etc _{base_url}/artikels?search[tgl_upload][start]=2016-01-01&search[tgl_upload][end]=2020-01-01_
- search using keyword _{base_url}/artikels?search[judul]=membangun%25_ or _{base_url}/artikels?search[judul]=%25membangun%25_ will produce like operator on query
- Search with multiple column

  ```curl -X GET "http://localhost:8080/wpOptions?search[option_name]=%mailserver%&search[autoload]=yes&search[option_id][start]=17&search[option_id][end]=18&limit=20" -H  "accept: application/json" -H  "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODA4MCIsImlhdCI6MTM1Njk5OTUyNCwiZW1haWwiOiJ0ZXMiLCJuYmYiOjEzNTcwMDAwMDB9.Pg4laqjebIDLzoFDS0ySnOKOueKLWjnXwWILsSmVsVI"

  ```

  `http://localhost:8080/wpOptions?search[option_name]=%mailserver%&search[autoload]=yes&search[option_id][start]=17&search[option_id][end]=18&limit=20`

## Todo

- [x] create example using this application

## Copyright

easyApi dikembangkan dan dimaintain oleh [asligresik](https://github.com/pandigresik)

## Lisensi

Lisensi dari easyApi adalah [MIT License](LICENSE) namun proyek yang dibangun menyeseuaikan dengan kebijakan masing-masing
