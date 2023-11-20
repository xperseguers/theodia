.. _usage:

Usage
=====

This chapter describes how to take advantage of this extension from the user's
point of view.

In order to show upcoming events in your website, need need to add a plugin to
a page:

.. image:: Images/plugin-calendars.png
   :alt: List of calendars to show in a given plugin

The plugin will show a list of calendars to choose from. You can select one or
more calendars to show in the plugin. Showing multiple calendars is useful if
you put the plugin on some homepage where all events from all your various
places of worship should be presented.

.. note::

   If you miss some place of worship, you will need to adapt the mapping as
   described in chapter :ref:`configuration`.

Events are related to a location (or place of worship). Upon first encounter,
this extension will automatically create a new place of worship when it
encounters a new one in the calendar. Those places are fetched from theodia and
stored at the root of the TYPO3 install (``pid=0``). Once imported, you may move
that record wherever you want and edit it freely.
