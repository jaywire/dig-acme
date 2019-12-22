# dig-acme

This CLI PHP script is an internal project for my own environment, but the purpose of this script is to check for a valid "_acme-challenge" CNAME record when using DNS-01 validation with LetsEncrypt. This is a port of my previous project, acme-check. I took what I learned from that project and decided to use "dig" instead of the PHP function "dns_get_record" due to being able to define the DNS server to use for the lookup. 

This was a tool for our Tier 3 Support Technicians to verify our clients entered the DNS-01 TXT record correctly on their end. Typically, we host DNS for our customers but in some cases they maintain their own DNS, so we have to send them the records required for our platform to work on their domain.

For LetsEncrpyt certfiicates, there are multiple validation methods. We use DNS-01 for our platform and this tool allows you to verify the record is entered correctly, and also does some overly complex error checking and provides feedback to the user. Much of the error handling could be stripped out, but this tool is used by people who may not be DNS experts.  

DNS-01 validation will happily follow a CNAME as if it were a TXT record, as long as you use the same formatting as required for the TXT record. We instruct customers to enter a record pointing back to a unique record in our DNS zone. This way, when we provision the certificate, we can place the TXT record into our own DNS server with the validation string. 

It looks a little like this:

In clients DNS: 

_acme-challenge.customerdomain.com "IS AN ALIAS OF" _acme-challenge.domainwecontrol.dev

In our DNS: 

TXT _acme-challenge.domainwecontrol.dev returns "667drNmQL3vX6bu8YZlgy0wKNBlCny8yrjF1lSaUndc"


# Why?

This is useful in a situation where you may not control the DNS zone you are managing. Due to the volatility of LetsEncrpyt Certificates (90 day expiration period), it can be troublesome to coordinate with the person(s) who control another domain and have them place the TXT validation record, and also count on them to place it correctly. Having them place a CNAME record once is much easier in this case. 

The only thing you will need to do is adjust the variable for $dmgdev to something within your DNS zone. I suggest creating a TXT record for the domain.dev._acme-dns.yourzone.com and placing your TXT value here. This can be automated, but that is outside the scope of this project.

By the above logic, that means your value for $dmgdev is ._acme-dns.yourzone.com


For now, this script will do the following:

1. Validate the domain through a RegEx. If no valid domain is provided, it will continue to loop you through the process until a valid domain that passes the check is provided.
2. Ask what DNS server you would like to use for this lookup. This is optional, and a null value will default to whatever $dnsDefault is defined as within the script. If something is entered here, it must be an IPv4 address and will be validated with a regex. Same as with Step 1, if an invalid IPv4 address is provided, it will loop you through until you either accept the default or provide a valid IPv4 address.
3. Check the domain for a response from a CNAME record with a HOST value in the proper format: _acme-challenge.domain.dev
4. Check the TARGET portion against your provided values $domain and $dmgdev - domain.dev._acme-dns.yourzone.com
5. Check for issues such as a target mismatch, a wildcard response, or a double-entry on the HOST portion of the record.


# To Use:

Place the file with no extension (acme) in your usr/bin/ directory and make sure you have proper perms set (0755) before running. To run, type "acmedig" into your terminal. 