..  _quality-gates:

Quality gates
=============

A quality gate reviews the generated answer before it is returned. It is useful
for high-risk content, strict source requirements or complex pipelines.

Validation criteria
-------------------

A quality gate can check whether:

* the answer is based on retrieved context;
* the answer follows the requested language and tone;
* required source references are present;
* the answer contains unsupported claims;
* the answer admits missing context where needed.

Use quality gates carefully: they add latency and model cost.

