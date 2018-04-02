# HttpMessageForwarder
Simple (yet) flexilble message relier from an HTTP Json request, into socket/http

I've created this PHP page in order to circumvent one of the biggest limitations on Zipato's Socket, which is pretty much useless, as it doesn't let you send HEX messages neither see the responses.

While doing it, I've added some features that allows to batch several commands to be sent, as well as setting other parameters on each of them.

Features:
* Stack one or many sequential messages to be sent.
* Messages can have:
   * Delay before sending the message in milliseconds
   * Delay after sending the message in milliseconds
   * Amount of times the message will be sent
  
* Message type "socket":
  * Specify host address and port   
    * Onkyo: Send messages to Onkyo devices
    * RAW:   Send raw TCP Messages
      * ASCII: Sends the message as is
      * HEX:   Sends Hexadecimal messages
* Message type "http" or "https":
  * Specify protocol: http or https
    * Specify host and port
    * Specify path ot the resource (ie, page.php or path/to/page.php)
    * Method: GET, POST, PUT
    * Params: List of parameter name and parameter value

# Instructions
1. Place the PHP file on your web server
2. Be sure you can reach it from Zipato or a computer connected on the same network
3. Adapt the sequence of "endpoints" to fit your needs.
4. Send the JSON string on the body of the request (check postman example).

(Note: You can use [Postman](https://www.getpostman.com/) to debug your messages)

# Example 1: Turn on ONKYO receiver on FM Tuner
```json
{
    "endpoints": 
    [
        {
            "type": "socket",
            "host": "192.168.0.1",
            "port": 60128,
            "commands": [
                {
                    "type": "onkyo",
                    "params": "24",
                    "content": "ascii",
                    "beforeDelay": 0,
                    "message": "SLI",
                    "afterDelay": 100,
                    "repeats": 10
                }
            ]
        }
     ]
}
```
# Example 2: Turn on ONKYO receiver on FM Tuner and set the volume to 20:
```json
{
    "endpoints": {
        "host": "192.168.2.141",
        "port": 60128,
        "commands": [
            {
                "type": "onkyo",
                "params": "24",
                "content": "ascii",
                "beforeDelay": 0,
                "message": "SLI",
                "afterDelay": 300,
                "repeats": 10
            },
         {
                "type": "onkyo",
                "params": "20",
                "content": "ascii",
                "beforeDelay": 0,
                "message": "MVL",
                "afterDelay": 300,
                "repeats": 10
            }
        ]
    }
}
```
# Example 3: Turn on ONKYO receiver on FM Tuner, set the volume to 20 and call an URL:
### (That URL might be the one of a virtual switch on your zipato, or another HTTP endpoint)
```json
{
    "endpoints":
    [
        {
          "host": "192.168.2.141",
          "port": 60128,
          "commands": [
              {
                  "type": "onkyo",
                  "params": "24",
                  "content": "ascii",
                  "beforeDelay": 0,
                  "message": "SLI",
                  "afterDelay": 300,
                  "repeats": 0
              },
              {
                  "type": "onkyo",
                  "params": "20",
                  "content": "ascii",
                  "beforeDelay": 0,
                  "message": "MVL",
                  "afterDelay": 300,
                  "repeats": 5
              }
          ]
        },
        {
            "type": "http",
            "host": "www.theurltoyourswitchorbox.com",
            "port": 80,
            "path": "thepage.jsp",
            "method": "post",
            "params": [
                { "name": "p1", "value": "1"},
                { "name": "p2", "value": "2"},
                { "name": "p3", "value": "3"}
            ],
            "beforeDelay": 0,
            "afterDelay": 100,
            "repeats": 10
        }
    ]
}
```
*Disclaimer*: My experience on PHP is practically none, I've managed to assemble this with a couple fo searches on Google, however if you wish to contribute and make it more flexible, then the help is more than welcome!.
