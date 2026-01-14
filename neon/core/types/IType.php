<?php

namespace neon\core\types;

/*
| ----------------------------
| Functions defining data flow
| ----------------------------
|
| These functions define how data flows form the user (typically a user http request) through to the database
| and from the database back to the user to be displayed.
| Often data must be decorated, validated, sanitised, transformed.
| Link fields, for exmaple, store data in the database as a string that represents a uuid that is the foreign key of another table.
| To achieve this mapping a Type + plus a configuration (the table the link field represents) is important.
| Or perhaps a location field.  In the database, a text field representing json stores a lattitude and longitude, the front end a map component is presented.
| Whilst the databse technically allows any text, the type validates the data to ensure only the correct shcema is stored.
| A type also has configuration
|
|      +---------------------+ Http  <-----------------------------+
|      |                                                           |
|      |                   +----------+                            |
|      +--setFromRequest-->|          |---->| Input Field |--------+
|                          |          |
|                          |          |
|                          |          |---->| Display | (context)
|                          |          |
|                          |          |
|                          |   Type   |---->| Process Filter |
|                          |          |
|                          |          |
|                          |          |---->| Filter Type |
|                          |          |
|                          |          |
|      +-----setFromDb---->|          |--getData()-->| Daedalus |--+
|      |                   +----------+                            |
|      |                                                           |
|      +-----------------------------------------------------------+
|
*/

/**
 * Types - application types
 */
interface IType
{
	public function setFromDb();

	public function setFromRequest();

	public function getForDb();

	public function getFilterInputField();

	public function renderField();

	public function renderView();

	public function getDisplayForSummary();

	public function getDisplayGrid();

	public function getDisplayEditable();

	public function appendFilter(IQuery $query, $searchData);

	public function getFilterField(); // IType
}