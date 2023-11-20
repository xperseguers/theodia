.. include:: ../Includes.rst.txt
.. _configuration:

Configuration
=============

In order to use this extension, you must configure which places of worship you
intend to use. This is done by editing your site configuration
(stored in :file:`config/sites/<site>/config.yaml`), and adding a list of
theodia calendars.

The easiest way to do this is to use the site configuration editor in the
Backend, which will automatically add the correct configuration for you. To do
so, go to the Backend and then:

- Open Site Management > Sites
- Find the site you want to configure and click the pencil icon to edit it
- Go to the "theodia" tab
- Add a mapping for each calendar you want to use in this site.

.. image:: Images/configuration.png
   :alt: Configuration of calendars to use

Each calendar you want to use is of the form ``<id>, <name>`` where ``<id>`` is
the ID of the calendar in theodia.

A sample configuration could be::

   148, Église Sts Pierre-et-Paul, Marly
   150, Église de Praroman, Le Mouret

You can find the ID of a calendar by searching it on
https://theodia.org/en/widget, selecting it in the list, and looking at the
generated code snippet.

This mapping is used to generate the list of available calendars to be used with
:ref:`the plugin <usage>`:

.. image:: ../Usage/Images/plugin-calendars.png
   :alt: List of calendars to show in a given plugin
