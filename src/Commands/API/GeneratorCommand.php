<?php

/**
 * CodeIgniter.
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author     CodeIgniter Dev Team
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 *
 * @see       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace asligresik\easyapi\Commands\API;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Autoload;
use Config\Services;

/**
 * Creates a new seeder file.
 */
class GeneratorCommand extends BaseCommand
{
    /**
     * The group the command is lumped under
     * when listing commands.
     *
     * @var string
     */
    protected $group = 'Generators';

    /**
     * The Command's name.
     *
     * @var string
     */
    protected $name = 'api:generate';

    /**
     * the Command's short description.
     *
     * @var string
     */
    protected $description = 'Creates api endpoint generator.';

    /**
     * the Command's usage.
     *
     * @var string
     */
    protected $usage = 'php spark api:generate or php spark api:generate -p Modules/Api/';

    /**
     * the Command's Arguments.
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * the Command's Options.
     *
     * @var array
     */
    protected $options = [
		'-p' => 'set basepath',
	];

    private $basePath;
    private $db;
    private $routes = [];
    private $numericType = ['int', 'tinyint', 'mediumint', 'bigint'];
    private $decimalType = ['decimal', 'float', 'double'];
    private $dateType = ['date', 'datetime'];

    /**
     * Generate api.
     */
    public function run(array $params)
    {              
        $this->setBasepath($params);
        helper('inflector');
        $this->db = db_connect();
        $table = CLI::prompt('Type your name table to generate (table_name [using , as separator example users,posts,jobs] / all)?');
        CLI::write('Please wait, reading data from database .....');
        $listAllTables = $this->listTable();
        $listTables = [];
        if ('all' != $table) {
            $selectedTables = explode(',', $table);
            foreach ($selectedTables as $t) {
                if (in_array(trim($t), $listAllTables)) {
                    array_push($listTables, trim($t));
                }
            }
        } else {
            $listTables = $listAllTables;
        }

        foreach ($listTables as $t) {
            CLI::write('Table name '.$t.' found', 'green');
            $this->generateApi($t);
        }

        if (!empty($this->routes)) {
            CLI::write('Add below route to app/Config/Routes.php');
            foreach ($this->routes as $route) {
                CLI::write($route, 'green');
            }
        }
        		
		
    }

    private function setBasepath($params){
        $config = new Autoload();  
        $this->basePath = $config->psr4[APP_NAMESPACE];        
        $path       = $params['-p'] ?? CLI::getOption('p');
		

        if (!empty($path)) {
           $this->basePath .= $path;
        }
    }

    /**
     * Write a file, catching any exceptions and showing a
     * nicely formatted error.
     */
    protected function writeFile(string $path, string $content)
    {
        $config = new Autoload();
        $appPath = $this->basePath;

        $directory = dirname($appPath.$path);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        try {
            write_file($appPath.$path, $content);            
        } catch (\Exception $e) {
            $this->showError($e);
            exit();
        }

        $path = str_replace($appPath, '', $path);

        CLI::write(CLI::color('  created: ', 'green').$path);
    }

    private function listTable(): array
    {
        return $this->db->listTables();
    }

    private function generateApi(string $tableName)
    {
        helper('filesystem');
        $modelName = $this->getModelName($tableName);
        $entityName = $this->getEntityName($tableName);
        $controllerName = $this->getControllerName($tableName);
        $fields = $this->db->getFieldData($tableName);
        $primaryKey = 'id';
        $allowFields = [];
        $validationRules = [];
        $docPropertySchema = [];
        foreach ($fields as $field) {
            $validation = [];
            if (in_array($field->type, $this->numericType)) {
                array_push($validation, 'numeric');
            }
            if (in_array($field->type, $this->decimalType)) {
                array_push($validation, 'decimal');
            }
            if (in_array($field->type, $this->dateType)) {
                array_push($validation, 'valid_date');
            }
            if ($field->max_length) {
                array_push($validation, 'max_length['.$field->max_length.']');
            }
            
            if(property_exists($field, 'nullable')){
                if (!$field->nullable) {
                    array_push($validation, 'required');
                }    
            }
            
            if ($field->primary_key) {
                $primaryKey = $field->name;
                array_push($validation, 'is_unique['.$tableName.'.'.$field->name.',id,{id}]');
            } else {
                array_push($allowFields, $field->name);
            }
            if (!empty($validation)) {
                array_push($validationRules, "'".$field->name."' => '".implode('|', $validation)."'");
            }

            array_push($docPropertySchema, $this->generateDocProperty($field));
        }
        $docPropertySchemaStr = $this->swaggerSchemaDoc($modelName, $docPropertySchema);
        $dataSource = [
            'tableName' => $tableName,
            'entityName' => $entityName,
            'modelName' => $modelName,
            'controllerName' => $controllerName,
            'primaryKey' => $primaryKey,
            'allowFields' => "'".implode("',".PHP_EOL."		'", $allowFields)."'",
            'validationRules' => implode(','.PHP_EOL.'		', $validationRules),
            'docPropertySchema' => $docPropertySchemaStr,
        ];
        $this->createEntity($dataSource);
        $this->createModel($dataSource);
        $this->createController($dataSource);
        $this->appendRoute($controllerName);
    }

    private function getModelName($tableName): string
    {
        return pascalize($tableName).'Model';
    }

    private function getEntityName($tableName): string
    {
        return pascalize($tableName);
    }

    private function getControllerName($tableName): string
    {
        return pascalize(plural($tableName));
    }

    private function replaceTemplate($stub, $search, $replace)
    {
        return str_replace(
            $search,
            $replace,
            $stub
        );
    }

    private function createModel(array $dataSource)
    {
        CLI::write('Generate model '.$dataSource['modelName']);        
        $template = $this->getTemplateFile('model.stub');
        $replaceData = [
            'modelName' => $dataSource['modelName'],
            'tableName' => $dataSource['tableName'],
            'entityName' => $dataSource['entityName'],
            'primaryKey' => $dataSource['primaryKey'],
            'allowFields' => $dataSource['allowFields'],
            'validationRules' => $dataSource['validationRules'],
        ];
        $dataFile = $this->replaceTemplate($template, ['{{modelName}}', '{{tableName}}', '{{entityName}}', '{{primaryKey}}', '{{allowFields}}', '{{validationRules}}'], $replaceData);
        $path = 'Models/'.$dataSource['modelName'].'.php';
        $this->writeFile($path, $dataFile);
    }

    private function createEntity(array $dataSource)
    {
        CLI::write('Generate entity '.$dataSource['entityName']);        
        $template = $this->getTemplateFile('entity.stub');
        $replaceData = [
            'entityName' => $dataSource['entityName'],
            'swaggerDoc' => $dataSource['docPropertySchema'],
        ];
        $dataFile = $this->replaceTemplate($template, ['{{entityName}}', '{{swaggerDoc}}'], $replaceData);
        $path = 'Entities/'.$dataSource['entityName'].'.php';
        $this->writeFile($path, $dataFile);
    }

    private function createController(array $dataSource)
    {
        CLI::write('Generate controller '.$dataSource['controllerName']);
        $template = $this->getTemplateFile('controller.stub');
        $replaceData = [
            'controllerName' => $dataSource['controllerName'],
            'modelName' => $dataSource['modelName'],
            'tag' => $dataSource['entityName'],
            'routeName' => lcfirst($dataSource['controllerName']),
        ];
        $dataFile = $this->replaceTemplate($template, ['{{controllerName}}', '{{modelName}}', '{{tag}}', '{{routeName}}'], $replaceData);
        $path = 'Controllers/'.$dataSource['controllerName'].'.php';
        $this->writeFile($path, $dataFile);
    }

    private function appendRoute(string $controllerName)
    {
        array_push($this->routes, '$routes->resource(\''.lcfirst($controllerName).'\');');
    }

    private function generateDocProperty($field)
    {
        $type = 'string';
        $format = '-';
        if (in_array($field->type, $this->numericType)) {
            $type = 'integer';
            $format = '-';
        }
        if (in_array($field->type, $this->decimalType)) {
            $type = 'number';
            $format = 'float';
        }
        if (in_array($field->type, $this->dateType)) {
            $type = 'string';
            $format = 'date';
        }
        $head = <<<DOC
	/**
	 * @OA\\Property(		 		 		 
	 *     description="{$field->name}",
	 *     title="{$field->name}",
	 *     type="{$type}",
	 * 	   format="{$format}",	 
DOC;
        $additional = [];
        if(property_exists($field, 'nullable')){
            array_push($additional, '	 * 	   nullable='.($field->nullable ? 'true' : 'false').',');
        }        

        if ($field->max_length) {
            array_push($additional, '	 * 	   maxLength='.$field->max_length.',');
        }
        $foot = <<<'DOC'
	 * )
	 *		 
	 */
DOC;
        $fieldName = is_numeric($field->name) ? '_'.strval($field->name) : $field->name;
        $fieldName = preg_replace('/\s+/', '_', $fieldName);

        return $head.PHP_EOL.implode(PHP_EOL, $additional).PHP_EOL.$foot.PHP_EOL.'	private $'.$fieldName.';';
    }

    private function swaggerSchemaDoc($modelName, $docPropertySchema)
    {
        return implode(PHP_EOL, $docPropertySchema);
    }

    private function getTemplateFile($fileName){
        $config = new Autoload();
        $appPath = $config->psr4[APP_NAMESPACE];
        $filePath = $appPath."/Commands/API/template/{$fileName}";
        if(file_exists($filePath)){
            $template = file_get_contents($filePath);
        }else{
            $template = file_get_contents(__DIR__."/template/$fileName");
        }
        
        return $template;
    }
}
