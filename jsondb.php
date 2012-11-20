<?php
	/**
	 * JsonDB Class
	 *
	 * @author Diego Montt - https://github.com/demve/
	 *
	 * @version 0.1
	 */
	class JsonDB{
		protected $version = "0";
		protected $global=array();
		protected $options=array();
		protected $tables=array();
		protected $fk=array();
		protected $pk=array();
		protected $uk=array();
		protected $json_tables=array();
		protected $added_fields=array();
		protected $generated_tables=array();
		protected $sql=array();
		public function __construct($jsondb){
			$db=json_decode($jsondb,true);
			if(!is_null($db)){
				if(isset($db["global"])){
					$this->global=$db["global"];
				}
				$this->version = $db["version"];
				$this->json_tables = $db["tables"];
				$this->prepare_tables();				
			}else{
				throw new Exception("Error Processing jsondb content", 1);
			}
		}
		protected function prepare_tables(){
			foreach ($this->json_tables as $table_name => $table_array) {
				$this->add_table($table_name);
				$this->add_keys($table_name);
			}
			$this->generate_sql();
		}
		public function getSQL(){
			return join("\r\n",$this->sql);
		}
		protected function add_table($table_name){
			if(!in_array($table_name, $this->generated_tables)){
				$this->tables[$table_name]=array();
				$fields=$this->json_tables[$table_name]["fields"];
				foreach ($fields as $field_name => $field_properties) {
					if(is_string($field_properties)){
						if(isset($this->global["types"][$field_properties])){
							$this->tables[$table_name][$field_name]=$this->global["types"][$field_properties];
						}
					}else{
						$this->tables[$table_name][$field_name]=$field_properties;
					}
					$this->add_defaults_attributes($table_name,$field_name);
				}
				$this->generated_tables[]=$table_name;
			}
		}
		public function add_defaults_attributes($table_name,$field_name){
			foreach ($this->global["defaultsAttributes"] as $key => $value) {
				if(!isset($this->tables[$table_name][$field_name][$key])){
					$this->tables[$table_name][$field_name][$key]=$value;
				}
			}
		}
		protected function add_keys($table_name){
			$keys=$this->json_tables[$table_name]["keys"];
			foreach ($keys as $type => $key) {
				if($type=="pk"){
					$this->pk[$table_name]=$key;
				}
				if($type=="fk"){
					$this->fk[$table_name]=$key;
					foreach ($key as $k=>$fk) {
						if(!in_array($fk['table'], $this->generated_tables)){
							$this->add_table($fk['table']);
						}
						$field_name = $fk["table"]."_".$fk["column"];
						$field_properties=$this->tables[$fk["table"]][$fk["column"]];
						unset($field_properties["extra"]);
						$this->tables[$table_name][$field_name]=$field_properties;
						$this->fk[$table_name][$k]["field"]=$field_name;
						if(!isset($this->fk[$table_name][$k]['onDelete'])){
							$this->fk[$table_name][$k]['on_delete']=(isset($this->global['fkDelete']))?strtoupper($this->global['fkDelete']):"NO ACTION";
						}
						if(!isset($this->fk[$table_name][$k]['onUpdate'])){
							$this->fk[$table_name][$k]['on_update']=(isset($this->global['fkUpdate']))?strtoupper($this->global['fkUpdate']):"NO ACTION";
						}
					}
				}
				if($type=="uk"){
					$this->uk[$table_name]=$key;
				}
			}
		}
		protected function generate_sql(){
			foreach ($this->tables as $table_name => $fields) {
				$sql="";
				$pk = $this->pk[$table_name];
				$sql .= "CREATE TABLE IF NOT EXISTS `{$table_name}` (\r\n";
				$sql_temp=array();
				foreach($fields as $field=>$field_properties){
					$type = (isset($field_properties["type"]))?" ".strtoupper($field_properties["type"]):"";
					$attr = (isset($field_properties["attr"]))?" ".strtoupper($field_properties["attr"]):"";
					$extra = (isset($field_properties["extra"]))?" ".strtoupper($field_properties["extra"]):"";
					$null = (isset($field_properties["null"]))?(($field_properties["null"])?" NULL":" NOT NULL"):"";
					$default = (isset($field_properties["default"]))?" DEAFULT ".$field_properties["default"]:"";
					$sql_temp[]= " `{$field}`{$type}{$attr}{$null}{$default}{$extra}";
				}
				$sql_temp[]=" PRIMARY KEY(`{$pk}`)";
				if(isset($this->fk[$table_name])){foreach($this->fk[$table_name] as $fk){
					$sql_temp[]=" KEY `{$fk['field']}_fkid` (`{$fk['field']}`)";
				}}
				if(isset($this->uk[$table_name])){foreach($this->uk[$table_name] as $uk){
					$sql_temp[]=" UNIQUE KEY `{$uk}_UNIQUE` (`{$uk}`)";
				}}
				$sql.=join(",\r\n",$sql_temp);
				$sql.="\r\n) ENGINE = INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;\r\n";
				$this->sql[$table_name]=$sql;
			}
			foreach($this->fk as $table => $fk_array) {
				$sql="ALTER TABLE `{$table}`\r\n";
				$sql_temp=array();
				foreach($fk_array as $k=>$fk) {
					$const=" ADD CONSTRAINT `{$fk['table']}_{$table}` FOREIGN KEY (`{$fk['field']}`)";
					$refer=" REFERENCES `{$fk['table']}` (`{$fk['column']}`)";
					$on=" ON DELETE {$fk['on_delete']}  ON UPDATE {$fk['on_update']}";
					$sql_temp[]="{$const}{$refer}{$on}";
				}
				$sql.=join(",\r\n",$sql_temp).";\r\n";
				$this->sql["FK_{$table}"] = $sql;
			}	
		}
	}
