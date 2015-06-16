# protocol-webform
form which generates a pdf from the input and upon confirmation of the pdf stores the input to a database as well as sends the pdf via email to the customer

## Configuration
* import `webform_protocol.sql` for the database schema
* edit `config.ini` (set up database access, the webserver mail and an optional PDF template)
* if you wish to use a PDF template, replace the (almost) blank `/pdf/template.pdf`

## Usage
* the form is accessed via `/prot/protocol.html`
* clicking the submit button triggers client-side validation 
* if it passes, form data is sent to `/prot/validation.php` for server-side validation
* if this passes as well, form data is sent to `/prot/pdf_make.php` which will generate a PDF to preview the data and open a confirmation dialog
* if the user accepts the preview, the form data is written to the database and an email is sent to the email address the user provided in the corresponding form field 

***Note: The PDF preview will open in a tab/window. Your browser might suppress it, if configured that way.***
