..  _site-settings:

Site settings
=============

The ``ai_assistant`` site set provides the following settings. They can be
overridden in the site-specific ``settings.yaml`` file:

..  code-block:: yaml

    aiAssistant:
      useLegacyFrontend: false
      allowedAssistantProfiles:
        - 1000
      requestTokenTtl: 7200
      maxPayloadBytes: 524288

``aiAssistant.useLegacyFrontend``
    Boolean, default ``false``. If enabled, the legacy Fluid/JavaScript chat
    frontend is rendered instead of the Vue custom element. Keep this disabled
    to use the current Vue frontend.

``aiAssistant.allowedAssistantProfiles``
    List of assistant profile UIDs that may be used by public frontend chat
    requests. An empty list keeps the default unrestricted behaviour.

    ..  important::

        For security reasons, restrict this list to the profile UIDs that are
        intentionally exposed on the frontend. Do not leave it unrestricted in
        production when other assistant profiles exist. Profiles may expose
        different prompts, data sources, tools or processing pipelines.

``aiAssistant.requestTokenTtl``
    Integer, default ``7200``. Lifetime of signed frontend request tokens in
    seconds.

``aiAssistant.maxPayloadBytes``
    Integer, default ``524288``. Maximum size of the serialized runtime
    settings payload accepted by the chat endpoint.

The settings are available to TypoScript conditions as site settings. For
example, the legacy frontend can be selected conditionally with:

..  code-block:: typoscript

    [traverse(site('configuration'), 'settings/aiAssistant/useLegacyFrontend') == true]
        # Legacy-specific TypoScript
    [END]
