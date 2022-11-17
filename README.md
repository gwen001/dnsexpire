<h1 align="center">dnsexpire</h1>

<h4 align="center">PHP tool to test CNAME expiration date of a subdomain list.</h4>

<p align="center">
    <img src="https://img.shields.io/badge/php-%3E=5.5-blue" alt="php badge">
    <img src="https://img.shields.io/badge/license-MIT-green" alt="MIT license badge">
    <a href="https://twitter.com/intent/tweet?text=https%3a%2f%2fgithub.com%2fgwen001%2fdnsexpire%2f" target="_blank"><img src="https://img.shields.io/twitter/url?style=social&url=https%3A%2F%2Fgithub.com%2Fgwen001%2Fdnsexpire" alt="twitter badge"></a>
</p>

<!-- <p align="center">
    <img src="https://img.shields.io/github/stars/gwen001/dnsexpire?style=social" alt="github stars badge">
    <img src="https://img.shields.io/github/watchers/gwen001/dnsexpire?style=social" alt="github watchers badge">
    <img src="https://img.shields.io/github/forks/gwen001/dnsexpire?style=social" alt="github forks badge">
</p> -->

---

## Install

```
git clone https://github.com/gwen001/dnsexpire
```

## Usage

```
Usage: php dnsexpire.php [OPTIONS] -f <subdomain|input file>

Options:
	-a	set alert for result output, default=30 days
	-f	subdomains list source file
	-h	print this help

Examples:
	php dnsexpire.php -f example.com
	php dnsexpire.php -a 10 -f dns.txt
```

---

<img src="https://raw.githubusercontent.com/gwen001/dnsexpire/master/preview.png" />

---

Feel free to [open an issue](/../../issues/) if you have any problem with the script.  

