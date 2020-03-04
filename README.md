# Record

Record is an extension of <a href="https://github.com/outlawdesigns-io/Db">Db</a>. Db cleans and wraps queries while Record provides an abstract class that can be extend to represent generic database records.

## Requirements

While Record is abstract, its constructor should be called in the constructor of all concrete classes.

Record's constructor accepts 3 required and one optional parameters.

```
__construct($db,$table,$primaryKey,$id = null)
```

$db: Database Name

$table: Database Table

$primaryKey: The name of the record's primary key field.

$id: The unique identifier of the specific record you would like to construt.


## Usage

It is important to note that all public property names should correspond to their database column names.

Private or protected properties can be added and removed as desired.

```
require_once __DIR__ . '/record.php';

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

//initialize empty and create

$p = new Person();
$p->first_name = 'Sally';
$p->last_name = 'Jones';
$p->favorite_color = 'Blue';
$p->isAlive = false;
$p->create();

//initialize empty and create 2

$data = array("first_name"=>"Sally","last_name"=>"Jones","favorite_color"=>"blue","isAlive"=>false);
$p = new Person();
$p->setFields($data)->create();

//initialize existing and update

$p = new Person($id);
$p->isAlive = $p->isAlive ? false : true;
$p->update();




```
