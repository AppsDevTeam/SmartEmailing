# SmartEmailing

Easy way to interact with SmartEmailing API from PHP

## Installation

The best way to install this component is using [Composer](http://getcomposer.org/):

```sh
$ composer require adt/smartemailing
```

Then it is required to add the following lines to config.neon:

```
parameters:
	smartemailing:
		username: <smartemailing_username>
		token: <smartemailing_api_token>

services:
	- ADT\SmartEmailing(%smartemailing.username%, %smartemailing.token%)
```

## Usage

```contactInsert($email, $contactlists, $properties, $customfields)```

Insert a new contact into SmartEmailing lists. `$contactlists`, `$properties` and `$customfields` are arrays.

```contactUpdate($email, $contactlists, $properties, $customfields)```

Update an existing contact in SmartEmailing lists. `$contactlists`, `$properties` and `$customfields` are arrays.

```contactGetOneByEmail($email)```

Get an exisitng contact by email.

```contactGetOneByID($email)```

Get an exisitng contact by user's ID.





