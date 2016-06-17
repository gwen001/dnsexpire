# dnsexpire
PHP tool to test CNAME expiration date of a subdomain list  
Note that this is an automated tool, manual check is still required.  

```
Usage: php dnsexpire.php [OPTIONS] -d <domain|input file>

Options:
	-a	set alert for result output, default=30 days
	-f	domains list source file
	-h	print this help

Examples:
	php dnsexpire.php -d example.com
	php dnsexpire.php -a 10 -d dns.txt
```

I don't believe in license.  
You can do want you want with this program.  

