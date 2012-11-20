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

* a 'version' String, to identify changes -not implemented in the class yet-
* a 'global' object that keeps some settings over the global structure, inside this object, you can add:
	* 'types' object for shothand types (eg. the id of your tables)
		* inside this object the keys are the name of the custom types
		* each custom type is an field object (see below in the fields description), 
	* 'defaultsAttributes' object for your fields
	* 'fkUpdate' and 'fkDelete' strings for the default "ON DELETE" and "ON UPDATE" actions for your foreign keys
* a 'tables' object to add your tables, this are formatted in this way:
	* the keys inside the 'tables' object are the names of the tables
	* inside each table, there are two objects: 'fields' and 'keys'
		*'fields' is either a string (refering to a 'global.types' field)
* Example(not quoted on purpose)

        {
        	version:0.1,		//The Verison of your structure
        	global:{},			//The global options of your structure
        	tables:{			//The tables
        		tableName:{		//Each Table
        			fields:{},	//The fields
        			keys:{}		//The Keys
		        }
        	}
        }



