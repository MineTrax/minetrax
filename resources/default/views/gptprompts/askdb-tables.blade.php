Given the below input question and list of potential tables, output a comma separated list of all the table names that may be necessary to answer this question.
Question: {{ $question }}
Table Names: @foreach($tables as $table){{ $table->getName() }},@endforeach

