..  _ui-components:

UI components
=============

AI Assistant can render configured UI components inside streamed answers. The
AI returns a component identifier and JSON data; it does not return arbitrary
HTML or JavaScript.

Presets
-------

Create a ``UI component`` record and select a preset. The available presets are:

* ``select-list``
* ``link-list``
* ``link``
* ``buttons``
* ``progress-indicator``
* ``decorative``

Preset templates, schemas and rendering behavior are supplied by ``ai-core``.
The TYPO3 record only selects the preset, assigns it to a profile or pipeline
step, and can add a wrapper CSS class.

Custom components
-----------------

Select ``Custom`` only when a preset is insufficient. Custom records expose the
template, action JSON, data schema and placeholder fields. Templates use
controlled placeholders such as ``{{label}}`` and repeated blocks such as
``{{#options}}...{{/options}}``.

Buttons
-------

The ``buttons`` preset uses inline child records. Each button defines:

* a visible label;
* a prompt template;
* optional placeholders.

The technical action identifier is generated automatically. Button clicks use
the normal chat prompt flow.

Links
-----

``link-list`` and ``link`` render regular links. They use ``label`` and ``url``
and open in a new tab with ``noopener noreferrer``. They do not submit a prompt.

Pipeline scope
--------------

Components can be selected on the assistant profile and overridden per pipeline
step with ``inherit``, ``replace`` or ``extend``. Component instructions are
provided to the answer generator and quality gate.
