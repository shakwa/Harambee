# Harambee

This is a project that aims to make it supper easy for people to come together and contribute money for worthy causes. This project uses paypal as the main online payment platform.

## Installation

### Prerequisites

* You should setup a host on your web server for your local domain. For this you could also configure Laravel Homestead or Valet. 
* PHP 5.6 or Greater
* OpenSSL PHP Extension
* cURL
* PDO PHP Extension
* GD Library.
* Enabled function mbstring
* Tokenizer PHP Extension
* allow_url_fopen (PHP.INI) is ON
* XML PHP Extension
* MCrypt PHP Extension
 

### Step 1

Begin by cloning this repository to your machine, and installing all Composer & NPM dependencies.

```bash
git clone git@github.com:shakwa/Harambee.git
cd Harambee && composer install && npm install
```

### Step 2 Setting MySQL 

This script works with PHP 5.6 and MySQL 5. The first thing to do is:

    1.) Create a database
    2.) Create a user for database

### Step 3 Uploading Files

A) upload the folder "Script" to the "public_html" folder on your server.
B) Log into your phpMyAdmin and import the fundme.sql file located in the folder "Mysql" (NOTE: the database already must be created )
C) Installation 

Make sure PDO driver is enabled on your server, if not, you should ask your hosting provider activation of this driver, so that the script can work.

After you have uploaded all files can start to setup the files.

#### Database Connection/Mail Set Up

Open the file .env located in your root folder, with any text editor, e.g: NOTEPAD ( IMPORTANT: You can not find the file and you are using cPanel click show hidden files ) change the following parameters, for theirs:

Database Connection

  	```
  	DB_DATABASE=YOU_DATABASE
	DB_USERNAME=YOU_USERNAME
	DB_PASSWORD=YOU_PASSWORD
    ```
  

place all data without spaces
Save and close.

#### Mail Set Up
Open the file mail.php located in the folder config, with any text editor, e.g: NOTEPAD

        ```php
      	'from' => [
            'address' => 'no-reply@miguelvasquez.net',
            'name' => 'Fundme',
        ],
        ```  

Save and close.

D) Settings Admin Panel - top
Access to admin panel

- Just enter from http://yousite.com/login and enter the following credentials.

	User: admin@example.com
	Pass: 123456
  

### In admin panel you can:

*Change the site name
*Change the site title welcome
*Statistics
*Set keywords for the site. (SEO)
*Add a description (SEO)
*Create/Edit pages e.g.: Help, Privacy, etc.
*Payments Settings.
*See Donations
*Set up social accounts
*Manage members.
*Add / Edit members.
*Manage campaigns.
*Add / Edit Campaigns.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
