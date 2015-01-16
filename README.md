#Mouse
Small framework for bootstrapping programs whether they are simple command line scripts or large web applications.  It is designed to be lightweight, only load necessary modules as needed, and be simple to drop into existing code.


#Quick Start
Mouse uses individual modules that can be instantiated onto the \mouse\hole singleton using an unique key.  Get the singleton of the mouse hole on to a variable or part of the class.  The instance() function takes two arrays as its parameters.  Each array consists of a module object key to data pair.

	$settings = []; //Initialize blank settings for now.  They can be manipulated at any time.  Settings that are available at instatiation will be used immediately.
	$mouse = \mouse\hole::instance(
		[
			'output'	=> 'mouse\output\output', //Module object access Key => module name(\mouse\folder\file)
			'request'	=> 'mouse\request\http',
		],
		[
			'output'	=> [
				'cli_only'	=> false
			]
		]
	);
	$wantsNewsletter = $mouse->request->getBoolean('newsletter', "POST"); //Defaults to "REQUEST" for request variables.  Use "POST" to look only at POST variables.
	if ($wantsNewsletter) {
		$mouse->output->addOutput("Customer does want newsletter.");
	} else {
		$mouse->output->addOutput("Customer does not want newsletter.");
	}

The first array is a single depth array containing module object keys to their namespaced module names.  Module names consist of the mouse namespace, the module folder name(s), and finally the module file name.  The module file name and class name should match.

The second array is a variable depth array containing module object keys to the settings for those modules.

When a module is instantiated on the \mouse\hole singleton it is assigned on to the object using the module object key provided.  In the above example the HTTP request class is instantiated with the request module key and can be accessed through $mouse->request.