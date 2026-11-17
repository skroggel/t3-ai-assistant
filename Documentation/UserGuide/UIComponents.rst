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
* ``container``

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

Structural containers
---------------------

The ``container`` preset opens a structural content section. It accepts an
optional space-separated CSS class list in the ``class`` data field. The model
emits ``container`` once before a grouped section; it does not emit a separate
closing component. When another ``container`` is emitted, the frontend closes
the previous section and starts the next one. The final open container is
closed automatically when the response is complete.

The container renderer creates the closing tag automatically. The container tag
is controlled by the resolved ``tagName`` and does not need a separate closing
template field.

Inline custom elements
----------------------

Set ``tag_name`` to an outer HTML or registered custom-element tag. The existing
``render_mode`` determines placement: ``block`` renders a block custom element
and ``inline`` keeps it inside the current Markdown fragment. The component can
omit its HTML template and let the custom element render the JSON data itself:

.. code-block:: text

   identifier: inline-link
   template: ""
   render_mode: inline
   tag_name: ui-inline-link
   data_schema: {"label":{"type":"string"},"url":{"type":"string"}}
   placeholders: label, url

The response marker is embedded directly in Markdown:

.. code-block:: text

   (@@UI_COMPONENT {"type":"inline-link","identifier":"inline-link","data":{"label":"Source","url":"/example"}})

The registered element receives the serialized component data through its
metadata attribute and owns the final HTML rendering. A generic custom element
can read the payload like this:

.. code-block:: javascript

   const readComponentData = (element) => {
       try {
           const data = JSON.parse(element.getAttribute('data-ai-component-data') || '{}');
           return data && typeof data === 'object' ? data : {};
       } catch (error) {
           return {};
       }
   };

   const data = readComponentData(this);
   const label = data.label || '';
   const url = data.url || '';
