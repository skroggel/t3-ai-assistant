..  _data-logging:

Logging records
===============

Pipeline trace records store runtime events. They are intended for debugging,
not as permanent analytics storage.

Typical trace events include:

* step started;
* step finished;
* LLM request;
* LLM response;
* retrieval request;
* retrieval result;
* error.

Production installations should limit trace retention and mask sensitive data.

