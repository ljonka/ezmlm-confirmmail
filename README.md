# ezmlm-confirmmail
Send confirmation mail for subscribe or unsubscribe to ezmlm mailinglist

Place maillistapi.php to your webroot, for most uberspace users default options should work out of the box.

Usage:

To subscribe: 
http://your-domain.org/maillistapi.php?type=subscribe&list=listname&mail=user@mail.de

To unsubscribe:
http://your-domain.org/maillistapi.php?type=unsubscribe&list=listname&mail=user@mail.de

After that the user 'user@mail.de' will receive a confirmation mail with such a link:

http://your-domain.org/maillistapi.php?type=confirm&hash=78es5znc0



