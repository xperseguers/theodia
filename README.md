# theodia

This is the official TYPO3 extension for theodia.

## What is theodia?

Theodia is a tool for broadcasting and sharing schedules of catholic masses. Very complete, it is thought
and developed to be able to extend over several countries.

More info on [theodia.org](https://theodia.org).

## Installation

You may install this extension using Composer:

```bash
composer require causal/theodia
```

## Configuration

You need to configure the calendars you want to use in your TYPO3 website. In order to be able to do that
in the context of a multi-site TYPO3 installation, you can configure those calendars using the Sites module:

- Open Site Management > Sites
- Find the site you want to configure and click the pencil icon to edit it
- Go to the "theodia" tab
- Add a mapping for each calendar you want to use in this site.

Each calendar you want to use is of the form `<id>, <name>` where `<id>` is the ID of the calendar in theodia.

A sample configuration could be:

```
148, Église Sts Pierre-et-Paul, Marly
150, Église de Praroman, Le Mouret
```

You can find the ID of a calendar by searching it on https://theodia.org/en/widget, selecting it in the list,
and looking at the generated code snippet.
