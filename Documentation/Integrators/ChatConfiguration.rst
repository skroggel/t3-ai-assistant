..  _chat-configuration:

Chat configuration
==================

The frontend plugin groups user-facing options into the ``language`` and
``accessibility`` namespaces. TYPO3 resolves these settings before creating the
framework-independent ``ChatOptions`` object passed to AI Core.

Language
--------

``settings.language.responseLanguage``
    Optional fixed language name or BCP 47 tag. It overrides user selection and
    the current site language.

``settings.language.allowUserSelection``
    Shows the effective response language as a persistent control in the chat
    header. Activating it reveals language-neutral options for the browser and
    website languages plus a free text input with suggestions in their native
    scripts. Users may also enter any language that is not listed. The visible
    controls use icons and native language names; localized descriptions remain
    available to assistive technologies. Selecting the browser language requests
    a short AI-generated confirmation in that language. Free input is applied by
    its checkmark action or the Enter key and uses the same confirmation route.
    Returning to the website language also confirms the effective language when
    it represents an actual switch.

Accessibility
-------------

``settings.accessibility.plainLanguage``
    Adds plain-language requirements to answer generators and quality gates.
    It does not alter retrieval or query optimization.

Screen-reader semantics, keyboard support, text direction and the controlled
announcement of completed responses are always active. They are technical
requirements, not editor options.

Runtime flow
------------

The configuration follows one direction:

..  code-block:: text

    FlexForm / TYPO3 site language / optional user selection
        -> ChatOptionsResolver
        -> ChatOptions { responseLanguage, languageCode, plainLanguage }
        -> AssistantRequest
        -> answer-producing pipeline steps

The FlexForm and JavaScript constructor keep the nested ``language`` and
``accessibility`` namespaces for editor and frontend readability. The Core DTO
is deliberately flat because it only transports three normalized runtime
values. Prompt instructions remain in AI Core and are not assembled in the
template or JavaScript.

The language confirmation uses the orchestrator's direct interaction method. It
uses the assistant profile's configured connector and default model, but does
not execute pipeline processors, retrieval or vector-store queries and is not
written to conversation memory.
