<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
create table assist(id int not null auto_increment, unique_id varchar(100), email_id varchar(100),contact_no varchar(100), primary key(id));
SQLTEXT;
$installer->run($sql);
$installer->endSetup();
