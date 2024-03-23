<h1 align="center">MineTrax</h1>
<p align="center"><b>Beautiful & Rich Web & Analytics Suite for Minecraft Servers!</b></p>
<p align="center"><a href="https://minetrax.github.io">https://minetrax.github.io</a></p>

![image](https://minetrax.github.io/img/shots/hero-min.png)

![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/minetrax/minetrax/laravel-mysql.yml?label=Tests&style=for-the-badge&logo=circleci&logoColor=white)
![Discord](https://img.shields.io/discord/508594544598712330?label=Discord&logo=Discord&logoColor=white&style=for-the-badge)
![GitHub Releases](https://img.shields.io/github/v/release/minetrax/minetrax?include_prereleases&style=for-the-badge&logo=github&logoColor=white)
![License](https://img.shields.io/github/license/minetrax/minetrax?style=for-the-badge&logo=github&logoColor=white)

## About MineTrax
MineTrax is a web suite & analytics tool for your minecraft servers. Using it you can improve your server engagement by providing a unified dashboard for players to visit and view their player data, or you can keep everything private and use it only for analytics, choice is yours.

## Activity

![Alt](https://repobeats.axiom.co/api/embed/0aac0565a812462007f7d4b1612c47b546e09a48.svg "Repobeats analytics image")

## Requirements
- PHP 8.1+
- MySQL 5.7+ or MariaDB 10.2.7+
- Apache2 or Nginx
- NodeJS 12+
- Redis Server
- Git
- Composer
- Supervisor
- Sendmail

## Features
MineTrax has lot of features, some of them are listed below:
 - In-Depth Analytics for Server and Player (Intel)
 - Track player & server data and perform various analysis on them.
 - Player Rating System let you calculate the rank of player based on your own custom ranking algorithm.
 - Let users to register in website and link to ingame player character.
 - Social Logins with Fb, Twitter etc.
 - Show server live status.
 - Show server ingame chat and let your website visiters to chat to ingame players.
 - Let admin manage server directly from website (with permission based controlled access).
 - Let admin run Polls in website.
 - News and Announcements Section.
 - ShoutBox for those who like shouting. (►__◄)
 - Post Feed to post random stuff for others to like and comment.
 - Localization. Translate to any language you want.
 - Custom Navigation Menu. (Customize navbar as per your liking using drag and drop)
 - Custom Pages.
 - Custom Theme
 - Dark Mode. Ofcoz (✿◠‿◠)

## Upcoming
 - Ban Management System
 - Staff Recruitment System
 - Store System
 - Automated Server Events
 - Rewards & Achievements
 - Advertisements & Monetisations
 - more...

## Install
Check out official docs at [https://minetrax.github.io](https://minetrax.github.io)

## Queue Workers
Command for running queue in dev.
```
php artisan queue:work --queue=longtask,default redis-longtask --timeout=0 --sleep=3 --tries=3
```

## License
MIT License
