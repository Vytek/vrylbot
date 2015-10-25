# VrylBot
PHP Experimental Telegram Bot in PHP

VrylBot è un Bot Telegram sperimentale scritto in PHP.

Qui è possibile vederlo all'opera: [https://telegram.me/Vrylbot](https://telegram.me/Vrylbot)

Il bot attualmente funziona in modalità "Update" su di una macchina Ubuntu 14.04 sul cloud
di Digital Ocean.

Librerie utilizzate:

- Libreria base per la costruzione del BOT su Telegram [https://github.com/Eleirbag89/TelegramBotPHP](https://github.com/Eleirbag89/TelegramBotPHP)
- Per accesso Twitter: [http://github.com/j7mbo/twitter-api-php- ](http://github.com/j7mbo/twitter-api-php)
- Per accesso alle API di Agenzia Mobilità Comune di Roma (https://www.agenziamobilita.roma.it/en/od-servizi-real-time.html) [http://scripts.incutio.com/xmlrpc/](http://scripts.incutio.com/xmlrpc/)
- Per il comando /almanac viene utilizzata la seguente libreria: [https://github.com/davidmpaz/rest-curlclient-php](https://github.com/davidmpaz/rest-curlclient-php) A questo proposito vedere il sito: [http://openalmanac.altervista.org](http://openalmanac.altervista.org/)/

### Funzionamento ###

Lo script PHP gira come daemon/servizio grazie a questo meraviglioso tutorial:

[http://blog.bobbyallen.me/2014/06/02/how-to-create-a-php-linux-daemon-service/
](http://blog.bobbyallen.me/2014/06/02/how-to-create-a-php-linux-daemon-service/)

###TO DO###
1. Rendere operativo il tutto in WebHook
2. Aggiungere comandi per fermale/paline richieste usando Redis
3. Usare sistemi di caching per una risposta più veloce dei singoli comandi  

