# PHP JSONDB

## Creates SQL Database Structure from a JSON-type content (.jsondb)

---

### Introduction:
* PHP JSONDB was created to ease the creation of tables in a database
* Designed to keep the database structure readable, inside an json file
* This Class is not in active development, I'll ocationally update it 
* Not Tested, but I used it in PHP 5.2
* Licensed under the MIT Open Source Licence [link](http://opensource.org/licenses/MIT)

### TO DO:
* Documentation blocks in the PHP file
* Error Handling
* Multiple Keys in one index
* Default fields to all tables
	* (eg "created-at","modified-at")
* Update a database structure
* etc...

### The JSONDB SCHEMA

The JSONDB schema is very straight forward, all you need to add is:

* a *version* String, to identify changes -not implemented in the class yet-
* a *global* object that keeps some settings over the global structure, inside this object, you can add:
	* *types* object for shothand types (eg. the id of your tables)
		* inside this object the keys are the name of the custom types
		* each custom type is an field object (see below in the fields description), 
	* *defaultsAttributes* a field object for the defaults values of any field
	* *fkUpdate* and *fkDelete* strings for the default "ON DELETE" and "ON UPDATE" actions for your foreign keys
* a *tables* object to add your tables, this are formatted in this way:
	* the keys inside the *tables* object are the names of the tables
	* inside each table, there are two objects: *fields* and *keys*
		* *fields* is either a string (refering to a *global.types* field) or a field object
* a *field* object is an object with:
	* *type*: a SQL type string (eg: VARCHAR(32))
	* *attr*: a SQL attribute string (eg: UNSIGNED)
	* *extra*: a SQL extra string (eg: AUTO_INCREMENT)
	* *null*: a boolean for accepting null values (true) or not (false)
	* *default*: a SQL Default value string (eg: 100)
	* *only the "type" is required, all the rest are optionals*
* a *key* object is an object with:
	* *pk*: a string with the name of the field which is the PRIMARY KEY
	* *fk*: an array of FOREIGN KEY objects each one has four parameters
		* *table* an string with the name of the table
		* *column* an string with the name of the columns
		* *onUpdate* and *onDelete* strings with the action for the "ON DELETE" and "ON UPDATE"
		* *Foreign keys will generate a "table_column" column in the table that holds it*
	* *uk*: an array of strings with the names of the fields to be UNIQUE KEY

#### JSONDB Example:(not quoted on purpose)

    {
    	//The Verison of your structure
    	version:0.1,
    	//The global options of your structure
    	global:{
    		//The CustomTypes in this structure
    		types:{
    			//Every custom type name is an object
    			CustomType1:{
    				//this is a field object
    				type:"int(10)",
    				attr:"unsigned",
    				extra:"auto_increment",
    				null:false
    			},
    			etc...
    		},
    		//the defaults atributes for all fields
    		defaultsAttributes:{
    			//this is also a field object
    			null:true
    		},
    		//The default value for "ON UPDATE" of all foreign keys
    		fkUpdate:"no action",
    		//The default value for "ON DELETE" of all foreign keys
    		fkDelete:"cascade"
    	},
    	//The Tables
    	tables:{
    		//Each key is a table name
    		users:{
    			fields:{
    				//if the field is a string, it has to be a custom type defined in global
    				id:CustomType1,
    				//else the field is a field object
    				name:{
    					type:"string(45)",
    					null:false
    				}
    			},
    			keys:{
    				//the primary key
    				pk:id,
    				// the unike keys
    				uk:[name],
    				// the foreign keys
    				fk:[
    					//Creates user_type_id in the table
    					{
    						table:user_type,
    						column:id,
    						onDelete:"No Action"
    					}
    				]
    			}
    		}
    	}
    }

### The PHP CLASS

1. Include the jsondb.php file in PHP
2. Create a new JSONDB object with the jsondb content you want to use
3. Call the method GetSQL and the output is a string with the SQL Create Statement


     include_once("jsondb.php");
     $json_content=file_get_contents("example.jsondb");
     $jsondb=new JsonDB($json_content);
     echo $jsondb->getSQL();

