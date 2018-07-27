<p align="center"><img src="https://laravel.com/assets/img/components/logo-laravel.svg"></p>

# PwC Test opgave - API

API'et er bygget på PHP frameworket [Laravel](https://laravel.com/) version 5.6, og det er opbygget efter de specifikationer, der er opstillet af [JSON API](http://jsonapi.org/).

## Ressourcer
Alle API'ets ressourcer ligger på addressen /api, og følgende er tilgængelige: De der er markeret med *(JWT)* kræver en JSON Web Token når der kaldes på ressourcen, og *Admin* kræver at man har rollen admin:
```
GET     /events                                      - Hent informationer om alle arrangementerne
POST    /events                          (JWT/Admin) - Opret et arrangement
GET     /events/{id}                                 - Hent informationer om et arrangement
PATCH   /events{id}                      (JWT/Admin) - Opdater informationerne omkring et arrangement
DELETE  /events/{id}                     (JWT/Admin) - Slet et arrangement
GET     /events/{id}/users               (JWT/Admin) - Hent informationer om de tilmeldte brugere
GET     /events/{id}/relationships/users (JWT/Admin) - Samme som ovenfor
POST    /events/{id}/users                     (JWT) - Tilmeld et arrangement - en admin kan tilmelde flere brugere
DELETE  /events/{id}/users                     (JWT) - Afmeld et arrangement - en admin kan afmelde flere brugere
GET     /users                           (JWT/Admin) - Hent informationer om alle brugerne
GET     /users/{id}                      (JWT/Admin) - Hent informationer om en bruger
POST    /users/register                              - Registrer som ny bruger
GET     /users/{id}/events               (JWT/Admin) - Hent en brugers tilmeldte arrangementer
GET     /users/{id}/relationships/events (JWT/Admin) - Samme som ovenfor
```

## Biblioteker
Der er anvendt følgende Laravel biblioteker:

- [tymondesigns/jwt-auth](https://github.com/tymondesigns/jwt-auth) - til håndtering af bruger autentificering. Når en bruger logger ind via API'et, så tildeles en JSON Web Token, der kan bruges til at verificere at brugeren at logget ind.
- [spatie/laravel-permission](https://github.com/spatie/laravel-permission) - til håndtering af brugernes rettigheder. Der er opstillet to forskellige roller, "Admin" og "Customer", som brugerne kan tildeles, og hermed få rettigheder til forskellige ting.
- [tobscure/json-api](https://github.com/tobscure/json-api) - til at opstille HTTP Reponse beskeder der følger [JSON API](http://jsonapi.org/) specifikationer.
## Brugere
Der kan logges ind med brugerne:
- **Admin:** email: admin - password: admin
- **Customer:** email: j@j.dk - password: 123
