..  _assistant-profiles:

Assistant profiles
==================

Assistant profiles are reusable assistant configurations. They combine global
prompt sections with connection and retrieval defaults.

Prompt sections
---------------

Identity
    Who the assistant is, what it is responsible for and what it must not do.

Behaviour rules
    Tone, language, safety and interaction rules.

Retrieval rules
    How retrieved documents must be used and how missing context should be
    handled.

Output rules
    Formatting, length, source references and fallback text.

Profile design advice
---------------------

Use profile prompts for rules that apply to every step. Do not put query-only or
answer-only instructions into the profile if they belong to a step.

