..  _logging-and-debugging:

Logging and debugging
=====================

The backend module can show pipeline traces. Traces help answer questions such
as:

* Which pipeline step was executed?
* Which prompt was sent to the model?
* Which query was used for retrieval?
* Which context was available?
* How many tokens were used?
* Which step failed or returned unexpected output?

Recommended debug workflow
--------------------------

#. Start with a single user query.
#. Check the query optimizer request and response.
#. Check retrieval results and metadata fields.
#. Check the answer generator prompt.
#. Verify whether frontend sources are present.
#. Reduce or adjust context only after retrieval is correct.

Runtime configuration
---------------------

Logging settings should be suitable for the environment. Development systems can
log complete prompts and responses. Production systems should minimize logging
and mask sensitive content where possible.

