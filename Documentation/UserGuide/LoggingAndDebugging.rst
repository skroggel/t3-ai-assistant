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

Set ``Pipeline log mode`` under **AI Assistant > Configuration**. ``errors``
stores failed events; ``verbose`` stores complete traces, including pipeline
validation warnings. Inspect and filter those records under
**AI Assistant > Diagnostics**.

Optional PSR log output is written to ``var/log/tx_aiassistant.log``. General
TYPO3 errors are normally available in ``var/log/typo3_*.log``. Development
systems can log complete prompts and responses. Production systems should
minimize logging and mask sensitive content where possible.
