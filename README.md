# Record

Record is an extension of <a href="https://github.com/outlawdesigns-io/Db">Db</a>.

Db builds and executes queries while Record provides an abstract class that can be extended to represent generic database records.

## Requirements

While Record is abstract, its constructor should be called in the constructor of all child classes.

Record's constructor accepts 3 required parameters and one optional parameter.

```
__construct($db,$table,$primaryKey,$id = null)

/*
$db: Database Name
$table: Database Table
$primaryKey: The name of the record's primary key field.
$id: The unique identifier of the specific record you would like to construct.
*/
```


## Usage

When extending Record, it is important to note that all public property names should correspond to their database column names.

Public arrays will automatically be stored in the Database as comma separated strings.

Private or protected properties can be added and removed as desired.

```
//Example concrete class

require_once __DIR__ . '/Record.php';

class Person extends Record{
  const DB = 'example';
  const TABLE = 'people';
  const PRIMARYKEY = 'id';

  public $firstName;
  public $lastName;
  public $favorite_color;
  public $isAlive;

  public function __construct($id = null){
    parent::__construct(self::DB,self::TABLE,self::PRIMARYKEY,$id);
  }

}

//Initialize empty and create

$p = new Person();
$p->first_name = 'Sally';
$p->last_name = 'Jones';
$p->favorite_color = 'Blue';
$p->isAlive = false;
$p->create();

//Initialize empty and create 2

$data = new stdClass();
$data->first_name = "Sally";
$data->last_name = "Jones";
$data->favorite_color = "Blue";
$data->isAlive = false;

$p = new Person();
$p->setFields($data)->create();

//Initialize existing and update

$p = new Person($id);
$p->isAlive = $p->isAlive ? false : true;
$p->update();

//Delete

$p = new Person($id);
$p->delete();

```

## Methods

### create()

Save object to database.

#### Example
```
/*
Remember from our example definition that Person's primary key is 'id',
which we are not setting manually.
*/
$p = new Person();
$p->first_name = 'Sally';
$p->last_name = 'Jones';
$p->favorite_color = 'Blue';
$p->isAlive = false;
try{
  $p->create(); //Obj is saved to database. id is set.
  echo $p->id . "\n"; //We would pass this value into Person's constructor to recreate it later.
}catch(Exception $e){
  echo $e->getMessage() . "\n";
}
```

### update()

Save changes made to the properties of an object instantiated with and Id.

### delete()

Delete object from database.

### setFields($updateObj:stdClass)

Allows for dynamically setting an object's properties.

Does not perform any property name validation. Use with caution.

### cleanString($string:string)

Static. Escapes apostrophes and back slashes for MySQL.

### search($db:string,$table:string,$primaryKey:string,$key:string,$value:string)

Static. Performs a basic database search WHERE $key like '%$value%'. Return primary keys of matching records.

#### Example
```
$results = Person::search(Person::DB,Person::TABLE,Person::PRIMARYKEY,"last_name","Johns");

foreach($results as $result){
  $person = new Person($result);
}
```

### getAll($db:string,$table:string,$primaryKey:string)

Static. Returns all of the primary keys from a given database table.

#### Example
```
$results = Person::getAll(Person::DB,Person::TABLE,Person::PRIMARYKEY);

foreach($results as $result){
  $person = new Person($result);
}
```

### getRecent($db:string,$table:string,$primaryKey:string,$limit:int)

Static. Returns primary keys of recently created records. Assumes primary keys are sequential.

#### Example
```
$results = Person::getRecent(Person::DB,Person::TABLE,Person::PRIMARYKEY,10);

foreach($results as $result){
  $person = new Person($result);
}
```

### browse($db:string,$table:string,$key:string)

Static. Returns distinct values from a database table column, $key.

#### Example
```
print_r(Person::browse(Person::DB,Person::TABLE,'favorite_color'));
```

### count($db:string,$table:string)

Static. Returns the total number of records in a database table.

#### Example
```
print_r(Person::count(Person::DB,Person::TABLE));
```

### countOf($db:string,$table:string,$key:string)

Static. Performs a basic group by. Returns an associative array with keys corresponding to the values in column $key and values corresponding to the total number of records containing those keys.

#### Example
```
print_r(Person::count(Person::DB,Person::TABLE,'favorite_color'));
```
