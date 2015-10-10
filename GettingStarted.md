# User Model #

  * Setup a User active record with an integer ID returned by Yii::app()->user->id.`*`
  * Add an anonymous user in the database with ID 0 (and either set the User ID to default to 0 in the database, or in your UserIdentity file)

`*` An example of this type of setup is explained here: [Building a Blog System using Yii](http://www.yiiframework.com/doc/blog/1.1/en/start.overview)


# Database #

The next step is to install the database schema located in:
/data/poll.sql

Modify the table prefixes as needed, as well as the foreign key constraint referencing the user's ID.


# App Configuration #

In your application's configuration file, add the following code:

```
return array(
   ...
   'import' => array(
     'application.modules.poll.models.*',
     'application.modules.poll.components.*',
   ),
   'modules' => array(
     'poll' => array(
       // Force users to vote before seeing results
       'forceVote' => TRUE,
       // Restrict anonymous votes by IP address,
       // otherwise it's tied only to user_id 
       'ipRestrict' => TRUE,
       // Allow guests to cancel their votes
       // if ipRestrict is enabled
       'allowGuestCancel' => FALSE,
     ),
   ),
);
```


# Usage #

The Poll extension has the basic Gii-created CRUD functionality, as well as a portlet to load elsewhere.

To load the latest poll:
```
$this->widget('EPoll');
```

To load a specific poll:
```
$this->widget('EPoll', array('poll_id' => 1));
```