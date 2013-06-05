CharsetConvertor
================

Providing a configurable way to convert multiple files with different charsets to a specific charset at one time

array(
	'input_charset' => '',
	'output_charset' => '',

	'dirs' => array(
		array(
			'input_charset' => '',
			'output_charset' => '',
			'name' => '',

			'subdir' => 

			'file' => array(
				array(
					'name' => 'foo',
					'input_charset' => 'gbk',
					'output_charset' => 'utf8',
				),
				...
			),
		),
		...
	),

	'files' => array(
		array(
			'name' => 'foo',
			'input_charset' => 'gbk',
			'output_charset' => 'utf8',
		),
		...
	),
)










