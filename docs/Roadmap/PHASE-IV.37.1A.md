# Phase IV.37.1A — Pippin Escapes the Quotation Marks

The IV.37.1 endpoint registration was correct. Its regression assertion used a
double-quoted PHP string containing `$this->dungeonForgeAjax`, so PHPUnit
interpolated the test class property instead of looking for the literal source
text.

This corrective pass:
- escapes the `$` in the assertion so the expected provider source is literal;
- removes the resulting undefined-property warning;
- changes no production IV.37.1 behaviour.

Expected suite remains 1,204 tests.
