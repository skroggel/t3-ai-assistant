..  _security:

Security
========

AI Assistant processes user questions and project content. Review security and
privacy requirements before enabling it in production.

Recommendations
---------------

* Store provider credentials only in connection records.
* Mask sensitive data in pipeline logs where possible.
* Limit logged payload size.
* Avoid indexing personal data unless there is a clear legal and operational
  basis.
* Treat retrieved content as data exposed to the assistant.
* Review frontend source fields before exposing metadata to users.
* Restrict backend access to trusted administrators.

Prompt safety
-------------

Assistant and step prompts should clearly state that answers must be grounded in
retrieved context and that missing information must not be invented.

