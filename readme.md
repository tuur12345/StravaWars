# SEWT Project Repository

## Project URL
* [Main login page](https://a24sewt303.studev.groept.be/public)

---

## Website credentials
### Login with Strava
* Users must authenticate through the Strava OAuth flow to access the full functionality of the application.
* Once authenticated, users can view their personal activity data and begin conquering map regions.
### Guest Access (Facebook)
* For demonstration or testing without Strava, you can log in using a shared Facebook account
  - Email : tuur.colignon1@gmail.com
  - Password : software303


---

## Project overview
This project is a gamified web application built for the Software Engineering Web Technologies (SEWT) course. 
The application integrates with the Strava API and turns real-world running or cycling activities into in-game actions. 
Users claim territory on a map by running / cycling, represented by hexagons, and use earned "Stravabucks" to upgrade and protect their regions.

The application is designed with inspiration from games like Clash of Clans, encouraging strategic territory acquisition and defense.

## Implemented Features
* User Authentication & Sessions
    * OAuth-based login through Strava
    * Sessions are maintained using Symfony's security component
* Map and Territory Mechanics
    * The world map is divided into hexagonal tiles
    * Users claim tiles (hexagons) by running/cycling through them in real life
    * Map is loaded with hexagons from the database, including ownership and upgrade state
* Database Integration
    * User accounts and Strava data
    * Hexagon ownership, upgrades, and status
    * Inventory and currency balances
* Stravabucks Economy
    * Users earn Stravabucks by receiving kudos on their Strava activities
    * Stravabucks are used to: 
      * Buy and apply upgrades to claimed hexagons
      * Purchase items in a virtual inventory
* Hexagon Upgrades
    * Claimed hexagons can be upgraded in multiple tiers, upgrades make a hexagon harder for others to conquer
* Game Mechanics
    * Only users who pass through a hexagon's GPS region can attempt to claim it
    * Hexagons can be defended using strategic upgrades
    * Similar to mobile strategy games, territory is valuable and must be defended actively
* Responsive UI
    * The website supports desktop and mobile use.
* Technologies Used
    * Symfony (PHP framework)
    * Twig (Templating engine)
    * MySQL (Database)
    * Strava API (Authentication and activity data)
    * Leaflet (For map rendering)
    * JavaScript (in-game currency and hexagon logic)
    * HTML/CSS (Frontend)

