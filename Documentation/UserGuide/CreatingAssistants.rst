..  _creating-assistants:

Creating assistants
===================

An assistant is configured through an assistant profile. The profile is the
place for rules that apply to the entire conversation.

Important profile fields
------------------------

Title
    Backend label for administrators and integrators.

Assistant label
    Name or label shown to users.

AI connection
    Provider used for chat completions and, depending on configuration,
    embeddings.

Vector store connection
    Vector database used for retrieval.

Collection
    Default collection searched by the assistant.

Identity prompt
    Stable identity of the assistant. Use this for role, scope and limits.

Behaviour rules
    General behaviour, tone, language handling and safety rules.

Retrieval rules
    Rules for using retrieved knowledge. This is where you define that retrieved
    documents are authoritative and that missing context must be acknowledged.

Output rules
    Final answer style, formatting, source handling and fallback behaviour.

Practical prompt separation
---------------------------

Keep global rules short and stable. Put highly step-specific instructions into
pipeline steps:

* search query rules belong to query optimizer steps;
* context selection rules belong to context optimizer steps;
* answer format rules belong to answer generator steps;
* validation rules belong to quality gate steps.

