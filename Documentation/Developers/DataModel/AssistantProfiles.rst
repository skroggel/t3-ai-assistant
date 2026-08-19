..  _data-assistant-profiles:

Assistant profile records
=========================

Assistant profile records store global assistant configuration:

* title and assistant label;
* AI connection reference;
* default vector store connection reference;
* introduction and initial message;
* identity, behaviour, retrieval and output prompts.

Profiles are intentionally separate from pipeline steps so the same global
assistant rules can be shared by multiple step types. Retriever steps may
override the profile's vector store connection and choose a collection from the
effective connection.
