# Web Crawler

 Simple web crawler that gets a news website as input (e.g. http://www.spiegel.de)
and crawls the HTML content of up to 100 pages of the site with a breadth-first approach.
 
 The downloaded pages are stored as HTML in a folder in the file system.
 
 The crawler is able to work with up to 50 parallel processes
(this number can be passed as a parameter, the default value is 5).

[Instructions]

Command to run:

php bin/crawl.php <url> <processes_amount>

where <url> is website URL
      <processes_amount> is amount of parallel processes (up to 50, default value: 5)

Example (with <processes_amount>):

php bin/crawl.php http://www.spiegel.de 5
