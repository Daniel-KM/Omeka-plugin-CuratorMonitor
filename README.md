Curator Monitor (plugin for Omeka)
==================================

[Curator Monitor] is a plugin for [Omeka] that allows a curator to monitor the
status of the collections. It creates an element set to manage administrative
status of records (transcription, translation, notes, etc.) and adds a view to
follow evolution of items. Element and terms can be added and modified at any
time.


Notes
-----

* Add, modify and remove elements

  - To manage  elements, go to Settings > Element Sets > Monitor > Edit.
  - Once inserted, the name and the description can't be changed via the
  interface like fields of other element sets. The comment and the other options
  can be updated at any time.
  - To remove an element, a security is added: a checkbox in the config page of
  the plugin should be checked before.

* Export of stats

  Stats can be filtered and exported via the main page of the plugin. Supported
  formats are:

  - CSV, with values separated by a tabulation.
  - [OpenDocument Spreadsheet] or "ods", the normalized format for
  spreadsheets, that  can be open by any free spreadsheets like [LibreOffice],
  or not free ones. This format requires that Zip to be installed on the server
  (generally by default).
  - [Flat OpenDocument Spreadsheet] or "fods", another standard format that can
  be opened by any free spreadsheets or by any text editor (this is a simple xml
  file). Note: With old releases of [LibreOffice] for Windows, a little free
  [filter] may need to be installed.


Installation
------------

Install the required plugins [HistoryLog], version 2.8 or higher, and [SimpleVocab].
If the last improvements on History Log are not yet committed, the [fork of History Log]
should be used.

Install the optional plugin [Hide Elements] in order to hide some fields to
the public or selected groups of users. The [fork of Hide Elements] can be used
too, because it allows more precise rules.

Uncompress files and rename plugin folder "CuratorMonitor".

Then install it like any other Omeka plugin.


Warning
-------

Use it at your own risk.

It’s always recommended to backup your files and your databases and to check
your archives regularly so you can roll back if needed.


Troubleshooting
---------------

See online issues on the [plugin issues] page on GitHub.


License
-------

This plugin is published under the [CeCILL v2.1] licence, compatible with
[GNU/GPL] and approved by [FSF] and [OSI].

In consideration of access to the source code and the rights to copy, modify and
redistribute granted by the license, users are provided only with a limited
warranty and the software's author, the holder of the economic rights, and the
successive licensors only have limited liability.

In this respect, the risks associated with loading, using, modifying and/or
developing or reproducing the software by the user are brought to the user's
attention, given its Free Software status, which may make it complicated to use,
with the result that its use is reserved for developers and experienced
professionals having in-depth computer knowledge. Users are therefore encouraged
to load and test the suitability of the software as regards their requirements
in conditions enabling the security of their systems and/or data to be ensured
and, more generally, to use and operate it in the same conditions of security.
This Agreement may be freely reproduced and published, provided it is not
altered, and that no provisions are either added or removed herefrom.


Contact
-------

Current maintainers:

* Daniel Berthereau (see [Daniel-KM] on GitHub)

First version of this plugin has been built for the [Jane Addams Papers Project]
of the Ramapo College of New Jersey.


Copyright
---------

* Copyright Daniel Berthereau, 2015


[Curator Monitor]: https://github.com/Daniel-KM/Omeka-plugin-CuratorMonitor
[Omeka]: https://omeka.org
[HistoryLog]: https://github.com/UCSCLibrary/HistoryLog
[fork of History Log]: https://github.com/Daniel-KM/Omeka-plugin-HistoryLog
[SimpleVocab]: https://github.com/omeka/plugin-SimpleVocab
[Hide Elements]: https://github.com/omeka/HideElements
[fork of Hide Elements]: https://github.com/Daniel-KM/Omeka-plugin-HideElements
[OpenDocument Spreadsheet]: http://opendocumentformat.org/
[LibreOffice]: https://www.libreoffice.org/
[Flat OpenDocument Spreadsheet]: https://en.wikipedia.org/wiki/OpenDocument_technical_specification
[filter]: http://www.sylphide-consulting.com/shapekit/spreadsheet-generation/15-opendocument-flat-format
[plugin issues]: https://github.com/Daniel-KM/Omeka-plugin-CuratorMonitor/issues
[CeCILL v2.1]: https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html
[FSF]: https://www.fsf.org
[OSI]: http://opensource.org
[Jane Addams Papers Project]: https://www.ramapo.edu/sshgs/the-jane-addams-papers-project
[Daniel-KM]: https://github.com/Daniel-KM "Daniel Berthereau"
