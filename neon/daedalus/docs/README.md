Neon Dynamic Data Service Overview
----------------------------------

The neon dynamic data service enables components to use data defined dynamically
by the application. Normally this is done using simple entity-attribute-value
tables, but this kind of implementation generally leads to problems.

Here we are using data definition tables as well as data object tables to manage
this properly. Described first is an overview of the data definition concepts
which are based simply on the idea of classes, members, types borrowed from
OO, and an additional idea of the actual database storage. Following that is
an explanation of how to actually create objects


Data Definition Tables
----------------------

There are 4 concepts (with corresponding tables) to understand:

. Class

. Member

. DataType

. Storage


Similarly to normal Object Oriented design, you can define "Class"es of data.
For example, if you are writing a blog website you might require a "blog" class
which defines what all blog objects can look like.

Classes are defined by a type, as well as a set of data "Members".
Continuing the example, a blog might consist of an author, a title, an
accompanying image, the published date and the article.

These members are of different data types.

"DataType"s can be created so that the concepts are suitable for the application
and not directly related to the data storage method. Data types have a set of
constraints which tell the UI how to constrain the input - e.g. this could
define the size of the input field, or whether or not dates can be in the past,
future or both. They are also associated with a storage mechanism which defines
where the data is actually stored in the database.

Continuing the example, you could create a data type of 'name' of say 100
characters long, an 'image' type of 200 characters, a 'title' type of 300
characters, a 'datepast' type for past dates and an 'article' type of say
100,000 characters.

"Storage" types are already fixed for applications and relate directly to the
available database types such as TEXT, VARCHAR etc. Each of the 'title', 'image',
'name' data types could map to the 'text_short' storage class. The 'datepast'
type would map to the date storage class, and the article would map to the
'text_long' storage class.


Building the Data Definition Content
------------------------------------

The storage definitions are predefined and shouldn't be changed by an
application. These should be created during installation.

Some data types will be pre-populated which can be used when defining class
members, and additional ones can be created as required for an application.
You use methods defined in the**IDdsDataTypeManagement*** interface to create or
remove data types.

Classes and class members are undefined and are built up by the application
before you can create objects of those types. You use methods defined in the
***IDdsClassManagement*** interface to create classes and their members.


Dynamic Objects and their Views
-------------------------------

Once a class has been defined, you can create objects of that type. E.g. once
you have defined the Blog class type, you can then add blogs to the system.

Classes will have a corresponding view that you can see in the database. Because
it is a view created through joins, you can use it for selects, but you cannot
update it directly.

To manage objects use the methods defined in the**IDdsObjectManagement*** interface.


Soft and Hard Deletions
-----------------------

It is possible to soft and hard delete classes, members, datatypes and objects.
To soft delete, use the appropriate delete method in the interface. A soft delete
can be undeleted using the appropriate undelete method in the interface. To hard
delete use the destroy methods. The essential difference is that soft deletes
appear to delete the item but doesn't actually remove any data from the database
whereas a hard delete does remove the data. Hard deletes cannot be undone.

**Soft deletes mean the following:**

Class: this removes the class from the available lists, removes the view table
and all objects of this class are no longer available.

Member: this removes the member from the available lists, removes the
corresponding column from the view and corresponding data from all objects.

Datatype: this removes the datatype from the available lists. Members already
using this datatype are unaffected.

Object: the object is removed from the view and all queries but data still
exists in the database

**Hard deletes mean the following:**

Class: destroys all object data of this class, and then destroys the class and
member definitions.

Member: destroys all corresponding object attribute data and then destroys the
member definition.

Datatype: cannot be destroyed while there are members using the datatype. It
will not purge the database of those members but rather leaves that up to the
user to fix.

Object: the object and all associated data is removed from the database.






## Requesting Objects

Obtaining objects is a two step procees - the first step is to place a
request for an object. This can be done many times building up a series
of requests. Once all the requests required have been placed then
calling commit will fetch the objects from the database and return them
in one go.

The reason for doing this is simply to reduce the number of database
calls that need to be made.

## Filter Definition

Filters allow you to create reasonably complex where clauses to select
the required objects. The interface here is easy enough to handwrite
 as well as more convenient for form based production.

## Concepts

 Filters are converted into the where clause for a normal SQL query. A
 reasonably complex clause could be

 WHERE ((field1 > 10 AND field1 <= 20) OR (field1 < 30)) AND field3 = 'x'.

 This statement can be broken down into clauses consisting of a field, an
 operator and a value. Each clause in the above is
   field1 > 10
   field1 <= 20
   field1 < 30
   field3 = 'x'
 This are then combined together using logic. These ideas are expanded
 below. Queries that can't be broken down this way can't be defined
 using this method.
 
## Detail

 A data request contains a 'Filters' parameter.

 'Filters' is a set of individual filter definitions. Each filter will be
 ORed together in the final query. Each filter consists of separate clauses
 and an optional logic defining how they are to be combined.

 In more detail ... (examples later)

 $filters  - these are an array of individual 'filter's [f1, f2] or if
 there is a single filter only, then it can just be the filter.

 $filter - this is an array of query clauses [ $C1, $C2, ...] or if there
 is only a single clause then it can just bs the clause.

 For multiple clauses an optional logic definition can also be provided.
 Each clause is AND'ed together unless the logic definition specifies
 otherwise.

 In more detail a filter is an array
 [
   $clause1,
   $clause2,
   ...,
   'logic' => if the filter is anything other than ANDing components
   then give each component a key and the combination logic here
   as a string of e.g.
     "('key1' OR ('key2' AND 'key3'))",
   and the keys will be replaced with the appropriate clauses
 ]

$clause - each clause is an array defined as
```
   array (
     [0] => the member_ref of the item
     [1] => one of the following operators
       '=' equals or if passed an array an IN => value
       '!=' not equals or for array NOT IN => value
       '>' greater than,
       '<' less than,
       '>=' greater or equal to,
       '<=' less than or equal to,
       'like' value for a text search comparison
       'not like' value for a text search comparison
       'null' set if testing for null [value not required]
       'not null' set if testing for not null [value not required]
     [2] => the value if required

     // and finally optionally
     [3] => key   key in logic string if provided in the filter
   )
```

## Examples

### 1. Simple single clause
 
 
```sql
WHERE 'name' = 'Jim Kirk'
```

Alternatives are:

```php
$filters = [ 'name', '=', 'Jim Kirk' ]
$filters = [ [ 'name', '=', 'Jim Kirk'] ]
$filters = [ [ [ 'name', '=', 'Jim Kirk' ] ] ]
```

### 2. Simple multi clause single filter

```sql
WHERE 'name' = 'Jim Kirk' AND 'date_of_birth' = '2233-03-22'
```

Alternatives are:

```php
$filters = [
    [ 'name', '=', 'Jim Kirk' ], [ 'date_of_birth', '=', '2233-03-22']
]
//or
$filters = [
    [ [ 'name', '=', 'Jim Kirk' ], [ 'date_of_birth', '=', '2233-03-22'] ]
]
```

### 3. Multiple filters
 
```sql
WHERE 'name' = 'Jim Kirk' OR 'name' = 'Spock'
```

```php
 $filters = [
   [0] => [ [ 'name', '=', 'Jim Kirk' ] ],
   [1] => [ [ 'name', '=', 'Spock'    ] ]
 ]
```

 or since this can be rewritten as
 
```sql
WHERE 'name' IN ('Jim Kirk', 'Spock')
```

```php
$filters = [ 'name', '=', ['Jim Kirk', 'Spock'] ]
```

### 4. Filter requiring logic string

 The example at the beginning was

 WHERE ((field1 > 10 AND field1 <= 20) OR (field1 < 30)) AND field3 = 'x'

 This requires a logic parameter to be added which maps to this. Each clause
 needs to be defined by a key so this can be reduced to a logic string of
   "((key1 AND key2) OR (key3)) AND key4"

```
 $filters = [
   [0] => [
     [ 'field1', '>',  10,  'key1' ],
     [ 'field1', '<=', 20,  'key2' ],
     [ 'field1', '<',  30,  'key3' ],
     [ 'field3', '=',  'x', 'key4' ],
     'logic' => "((key1 AND key2) OR (key3)) AND key4"
   ]
 ]
```

 The logic string is heavily constrained to prevent SQL injection. The only
 characters allowed after the keys, AND, OR and NOT are ( and ). Anything else
 will result in the request being rejected

## Data Conversions

 On saving of and retrieval of data, some conversion of data takes place.
 The following conversions are automatically performed
 
- 'boolean': this is converted from true/false to 1/0 on save and vice versa
- 'json': this is converted from an array to JSON on save and vice versa
- 'choice': this is converted from a key=>value pair to the key on save if the 
input was an array. On return it is converted back if the choice exists
in the member choice field otherwise it is just the saved value
 


