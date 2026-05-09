# Lab Android – GPS, API PHP & Google Maps

## Objectif

- Capturer la position GPS d'un appareil Android
- Envoyer les coordonnées vers un serveur PHP/MySQL via Volley (POST)
- Afficher toutes les positions enregistrées sur une Google Map avec des markers

---

## Prérequis

- **Serveur** : XAMPP / WAMP / LAMP (Apache + PHP + MySQL)
- **Android Studio** (dernière version) + appareil physique (GPS activé) ou émulateur
- **Réseau** : téléphone et PC sur le même Wi-Fi ou hotspot
- **Clé Google Maps API** (Maps SDK for Android activée dans Google Cloud)

---

## Architecture globale

```
Android App
├── MainActivity.java       → GPS + Volley POST → createPosition.php
└── MapsActivity.java       → Volley POST → showPositions.php → markers

Serveur PHP
localisation/
├── classe/Position.php
├── connexion/Connexion.php
├── dao/IDao.php
├── service/PositionService.php
├── createPosition.php      → INSERT en base
└── showPositions.php       → SELECT → JSON

MySQL
└── base : localisation
    └── table : position (id, latitude, longitude, date, imei)
```

---

## Partie 1 — Base de données MySQL

### Étape 1 — Créer la base
Dans phpMyAdmin (ou terminal MySQL) :
- Créer une base de données nommée **`localisation`**

### Étape 2 — Créer la table `position`
Exécuter le script SQL suivant :

```sql
CREATE TABLE `position` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `date` datetime NOT NULL,
  `imei` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
```

> ✅ **Checkpoint** : `SELECT * FROM position;` retourne un tableau vide.

---

## Partie 2 — Backend PHP

### Étape 3 — Créer l'arborescence

Dans le dossier web (`htdocs/` ou `www/`), créer la structure suivante :

```
localisation/
├── classe/Position.php
├── connexion/Connexion.php
├── dao/IDao.php
├── service/PositionService.php
├── createPosition.php
└── showPositions.php
```

### Étape 4 — `classe/Position.php` (modèle)

Créer la classe `Position` avec :
- 5 attributs privés : `id`, `latitude`, `longitude`, `date`, `imei`
- Un constructeur avec ces 5 paramètres (`id` sera `null` à l'insertion)
- Les getters et setters pour chaque attribut

> 💡 `id` est auto-incrémenté côté MySQL, Android n'a pas besoin de l'envoyer.

### Étape 5 — `connexion/Connexion.php` (PDO)

Créer la classe `Connexion` qui :
- Ouvre une connexion PDO vers MySQL dans le constructeur
- Paramètres : `host = localhost`, `dbname = localisation`, `login = root`, `password = ''`
- Active le mode `ERRMODE_EXCEPTION` pour ne pas masquer les erreurs SQL
- Expose la connexion via `getConnextion()`

### Étape 6 — `dao/IDao.php` (interface)

Créer l'interface `IDao` avec les 5 méthodes standards du CRUD :
- `create($obj)`, `update($obj)`, `delete($obj)`, `getById($obj)`, `getAll()`

> Seules `create` et `getAll` seront réellement implémentées dans ce lab.

### Étape 7 — `service/PositionService.php` (INSERT + SELECT)

Créer la classe `PositionService` qui implémente `IDao` :

- **`create(Position $position)`** :
  - Préparer un `INSERT INTO position(latitude, longitude, date, imei) VALUES (?, ?, ?, ?)`
  - Exécuter avec `$stmt->execute([...])` en utilisant les getters de l'objet
- **`getAll()`** :
  - Exécuter `SELECT * FROM position`
  - Retourner `fetchAll(PDO::FETCH_ASSOC)`
- Laisser les autres méthodes vides

> 💡 Les requêtes préparées (placeholders `?`) évitent les injections SQL et gèrent mieux les formats.

### Étape 8 — `createPosition.php` (endpoint INSERT)

Ce script reçoit les données Android en POST et insère une position :

1. Vérifier que la méthode est `POST`, sinon retourner une erreur 405
2. Lire les 4 champs POST : `latitude`, `longitude`, `date`, `imei`
3. Vérifier qu'aucun champ n'est `null`, sinon retourner une erreur 400
4. Créer un objet `Position(null, lat, lon, date, imei)` et appeler `$service->create()`
5. Retourner une réponse JSON `{"ok": true}` en cas de succès
6. Optionnel : inclure `$_SERVER['REMOTE_ADDR']` dans la réponse pour le debug réseau

> Ajouter en tête : `header('Content-Type: application/json; charset=utf-8');`

### Étape 9 — `showPositions.php` (endpoint SELECT → JSON)

Ce script retourne toutes les positions en JSON :

1. Vérifier que la méthode est `POST`
2. Appeler `$service->getAll()`
3. Encoder et retourner : `json_encode(["positions" => $results])`

> ✅ **Checkpoint** : tester avec Postman → POST sur `showPositions.php` → réponse `{"positions": []}` (vide au départ).

---

## Partie 3 — Application Android (GPS + Volley)

### Étape 10 — Créer le projet Android

- Android Studio → **New Project** → **Empty Views Activity**
- Nom : `Localisation`
- Langage : Java
- Min SDK : API 24

### Étape 11 — Ajouter la dépendance Volley

Dans `build.gradle (Module: app)`, ajouter :

```groovy
implementation 'com.android.volley:volley:1.2.1'
```

Synchroniser le projet (**Sync Now**).

### Étape 12 — Permissions dans `AndroidManifest.xml`

Ajouter les 4 permissions :

```xml
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />
<uses-permission android:name="android.permission.INTERNET" />
<uses-permission android:name="android.permission.READ_PHONE_STATE" />
```

Autoriser le trafic HTTP (non HTTPS) dans la balise `<application>` :

```xml
android:usesCleartextTraffic="true"
```

> ⚠️ `READ_PHONE_STATE` est souvent bloqué sur Android récent. Un fallback via `ANDROID_ID` est nécessaire.

### Étape 13 — `strings.xml` (messages localisés)

Ajouter 4 chaînes de caractères :
- `provider_enabled` : "The provider %s is now enabled"
- `provider_disabled` : "The provider %s is now disabled"
- `provider_new_status` : "The provider %1$s has now a new status %2$s"
- `new_location` : "New Location : Latitude = %1$s, Longitude = %2$s, Altitude = %3$s avec une précision de %4$s mètres"

### Étape 14 — Layout `activity_main.xml`

Créer un `LinearLayout` vertical contenant :
- `TextView` id `tvLat` — texte initial "Latitude: -"
- `TextView` id `tvLon` — texte initial "Longitude: -"
- `Button` id `btnMap` — texte "Afficher Map"

### Étape 15 — `MainActivity.java`

L'activité doit gérer dans cet ordre :

**Dans `onCreate()`** :
1. Initialiser les vues (`tvLat`, `tvLon`, `btnMap`)
2. Créer le `RequestQueue` Volley : `Volley.newRequestQueue(getApplicationContext())`
3. Récupérer le `LocationManager` via `getSystemService()`
4. Définir `insertUrl` = `http://<IP_DU_PC>/localisation/createPosition.php`
5. Au clic sur `btnMap` → démarrer `MapsActivity`
6. Appeler la méthode de vérification de permission

**Méthode `askLocationPermissionAndStart()`** :
- Vérifier `ACCESS_FINE_LOCATION` avec `ActivityCompat.checkSelfPermission()`
- Si accordée → appeler `startGpsUpdates()`
- Sinon → `requestPermissions()` avec code `100`

**Méthode `startGpsUpdates()`** :
- Appeler `locationManager.requestLocationUpdates()` avec :
  - Provider : `GPS_PROVIDER`
  - `minTime` : 60000 ms (1 minute)
  - `minDistance` : 150 mètres
- Dans `onLocationChanged()` :
  - Lire `lat`, `lon`, `alt`, `accuracy`
  - Mettre à jour `tvLat` et `tvLon`
  - Afficher un Toast avec le message `new_location`
  - Appeler `addPosition(lat, lon)`
- Dans `onProviderEnabled/Disabled()` : Toast avec les strings correspondantes
- Dans `onStatusChanged()` : convertir le statut en texte et afficher un Toast

**Méthode `addPosition(double lat, double lon)`** :
- Créer un `StringRequest` POST vers `insertUrl`
- Dans `getParams()`, construire la `HashMap` avec :
  - `latitude` et `longitude` (convertis en String)
  - `date` : format `"yyyy-MM-dd HH:mm:ss"` via `SimpleDateFormat`
  - `imei` : résultat de `getDeviceIdentifier()`
- Ajouter la requête au `requestQueue`

**Méthode `getDeviceIdentifier()`** :
- Tenter d'abord `Settings.Secure.ANDROID_ID` (stable sur Android récent)
- En fallback, tenter `TelephonyManager.getDeviceId()` si permission accordée
- Retourner `"UNKNOWN_DEVICE"` si aucune option ne fonctionne

**`onRequestPermissionsResult()`** :
- Code `100` + permission accordée → appeler `startGpsUpdates()`
- Permission refusée → Toast d'information

> ✅ **Checkpoint** : bouger (ou simuler une position sur émulateur) → une ligne apparaît dans phpMyAdmin.

---

## Partie 4 — Google Maps Activity (affichage des positions)

### Étape 16 — Ajouter Google Maps au projet

> ⚠️ Les versions récentes d'Android Studio n'ont plus le template **Google Maps Activity**.  
> Voici comment l'ajouter manuellement.

**Dépendances dans `build.gradle`** :

```groovy
implementation 'com.google.android.gms:play-services-maps:<version>'
```

**Clé API dans `AndroidManifest.xml`** (dans `<application>`) :

```xml
<meta-data
    android:name="com.google.android.geo.API_KEY"
    android:value="VOTRE_CLE_API_ICI" />
```

**Layout `activity_maps.xml`** : créer un layout avec un `SupportMapFragment` :

```xml
<fragment
    android:id="@+id/map"
    android:name="com.google.android.gms.maps.SupportMapFragment"
    android:layout_width="match_parent"
    android:layout_height="match_parent" />
```

### Étape 17 — Créer `MapsActivity.java`

La classe doit :
- Étendre `FragmentActivity` et implémenter `OnMapReadyCallback`
- Déclarer `showUrl` = `http://<IP_DU_PC>/localisation/showPositions.php`

**Dans `onCreate()`** :
1. Créer le `RequestQueue` Volley
2. Récupérer le `SupportMapFragment` via `getSupportFragmentManager().findFragmentById(R.id.map)`
3. Appeler `mapFragment.getMapAsync(this)`

**Dans `onMapReady(GoogleMap googleMap)`** :
- Stocker la référence `mMap = googleMap`
- Appeler `setUpMap()`

**Méthode `setUpMap()`** :
- Créer un `JsonObjectRequest` POST vers `showUrl`
- Dans le listener de succès :
  - Extraire le tableau `positions` : `response.getJSONArray("positions")`
  - Boucler sur chaque objet JSON
  - Lire `latitude` et `longitude`
  - Appeler `mMap.addMarker(new MarkerOptions().position(new LatLng(lat, lon)).title("Marker"))`
- Ajouter la requête au `requestQueue`

> ✅ **Checkpoint** : lancer `MapsActivity` → les markers apparaissent sur la carte aux positions enregistrées en base.

---

## Configuration réseau

| Paramètre | Valeur |
|---|---|
| IP du serveur | IP locale du PC (ex. `192.168.43.228`) |
| Port Apache | `80` (par défaut) |
| URL d'insertion | `http://<IP>/localisation/createPosition.php` |
| URL d'affichage | `http://<IP>/localisation/showPositions.php` |

> 💡 Pour trouver l'IP du PC : `ipconfig` (Windows) ou `ifconfig` (Linux/Mac).  
> Vérifier que le pare-feu Windows autorise Apache sur le réseau local.

---

## Problèmes fréquents

| Problème | Cause probable | Solution |
|---|---|---|
| Volley timeout / erreur réseau | Mauvaise IP ou pare-feu bloquant | Vérifier l'IP, tester l'URL dans un navigateur depuis le téléphone |
| `CLEARTEXT` communication refused | `usesCleartextTraffic` absent | Ajouter `android:usesCleartextTraffic="true"` dans `<application>` |
| GPS ne se déclenche jamais | Permission refusée ou émulateur sans localisation | Vérifier les permissions, simuler une position dans l'émulateur |
| `IMEI` null ou exception | Android 10+ bloque `getDeviceId()` | Utiliser `ANDROID_ID` en fallback |
| Carte blanche | Clé API incorrecte ou Maps SDK non activé | Vérifier la clé dans Google Cloud Console |
| Markers non affichés | Table vide ou JSON mal parsé | Vérifier `showPositions.php` avec Postman |
| `onProviderDisabled` ne se déclenche pas | Dépend du provider utilisé | Vérifier l'état du GPS avant de démarrer les updates |

---

## Résumé des fichiers créés / modifiés

| Fichier | Rôle |
|---|---|
| `localisation/classe/Position.php` | Modèle objet de la table `position` |
| `localisation/connexion/Connexion.php` | Connexion PDO à MySQL |
| `localisation/dao/IDao.php` | Interface CRUD |
| `localisation/service/PositionService.php` | INSERT et SELECT en base |
| `localisation/createPosition.php` | API endpoint : reçoit POST Android, insère en base |
| `localisation/showPositions.php` | API endpoint : retourne toutes les positions en JSON |
| `AndroidManifest.xml` | Permissions + cleartext + clé Maps API |
| `build.gradle` | Dépendances Volley + Maps |
| `strings.xml` | Messages GPS localisés |
| `activity_main.xml` | Layout avec TextViews et bouton Map |
| `MainActivity.java` | GPS + Volley POST + gestion permissions |
| `activity_maps.xml` | Layout avec SupportMapFragment |
| `MapsActivity.java` | Requête JSON + affichage markers sur carte |
