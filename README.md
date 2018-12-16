# Autodiscover
Mailserver Autodiscover for Outlook, Mozilla and most common Mail Clients.


originally based on https://github.com/Thorarin/MailClientAutoConfig by Marcel Veldhuizen.


Usage
-
```
$config = new Configuration();


// Name of your Configuration
$cfg = $config->add('example.com');
$cfg->name = 'Example mail services';
$cfg->nameShort = 'Example';
$cfg->domains = ['example.com', 'example.org'];
$cfg->username = $_GET['emailaddress'];

// If you do not use email addresses as usernames you may want to use you own UsernameResolver like this:
// $cfg->username = new AliasesFileUsernameResolver("/etc/mail/domains/$domain/aliases");

$cfg->addServer('imap', 'mail.example.com')
    ->withEndpoint('STARTTLS')
    ->withEndpoint('SSL');

$cfg->addServer('smtp', 'smtp.example.com')
    ->withEndpoint('STARTTLS')
    ->withEndpoint('SSL');
    
    
// check wether to use mozilla or outlook handler based on subdomain.
    
if (strpos($_SERVER['SERVER_NAME'], "autoconfig.") === 0) {

    // Configuration for Mozilla Thunderbird, Evolution, KMail, Kontact
    $handler = new MozillaHandler($config);

} else if (strpos($_SERVER['SERVER_NAME'], "autodiscover.") === 0) {

    // Configuration for Outlook
    $handler = new OutlookHandler($config);
    
}

$handler->handleRequest();

```