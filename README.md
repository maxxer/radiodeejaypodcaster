# radiodeejaypodcaster
Sorgenti delle pagine [Radio Deejay reloaded podcaster](http://deejayreloadedpodcast.maxxer.it/), degli script PHP per generare i podcast dei reloaded di [Radio Deejay](http://www.deejay.it)

## Setup

```
cat setup/base.sql | sqlite3 radiodeejayreloaded.sqldb
```

Per servire i podcast XML aggiungere questa regola di rewrite. 

Per nginx:
```
location /podcast {
    rewrite ^/podcast/([\-0-9a-z]+).xml$ /podcast.php?prog=$1;
}
```