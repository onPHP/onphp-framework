<?php

/***************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
final class OpenIdTest extends TestCase
{
    public function testCredentials()
    {
        $credential = OpenIdCredentials::create(
            HttpUrl::create()->parse('http://www.example.com/'),
            HttpClientStub::create(
                HttpResponseStub::create()->
                setStatus(new HttpStatus(HttpStatus::CODE_200))->
                setBody(<<<EOT
<html><head><link rel="openid.server"
                  href="http://www.myopenid.com/server" />
            <link rel="openid.delegate" href="http://example.myopenid.com/" />
</head></html>
EOT
                )
            )
        );

        $this->assertEquals(
            $credential->getServer()->toString(),
            'http://www.myopenid.com/server'
        );

        $this->assertEquals(
            $credential->getRealId()->toString(),
            'http://example.myopenid.com/'
        );

        // from openId creator blog
        $credential = OpenIdCredentials::create(
            HttpUrl::create()->parse('http://brad.livejournal.com/'),
            HttpClientStub::create(
                HttpResponseStub::create()->
                setStatus(new HttpStatus(HttpStatus::CODE_200))->
                setBody(<<<EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="alternate" type="application/rss+xml" title="RSS" href="http://brad.livejournal.com/data/rss" />
<link rel="alternate" type="application/atom+xml" title="Atom" href="http://brad.livejournal.com/data/atom" />
<link rel="service.feed" type="application/atom+xml" title="AtomAPI-enabled feed" href="http://www.livejournal.com/interface/atomapi/brad/feed" />
<link rel="service.post" type="application/atom+xml" title="Create a new post" href="http://www.livejournal.com/interface/atomapi/brad/post" />
<link rel="openid.server" href="http://www.livejournal.com/openid/server.bml" />
<meta http-equiv="X-XRDS-Location" content="http://brad.livejournal.com/data/yadis" />
<script src='http://www.livejournal.com/js/core.js' type='text/javascript'></script>
<script src='http://www.livejournal.com/js/dom.js' type='text/javascript'></script>
<script src='http://www.livejournal.com/js/httpreq.js' type='text/javascript'></script>

<script type='text/javascript'>
    function controlstrip_init() {
        if (! $('lj_controlstrip') ){
            HTTPReq.getJSON({
              url: "/brad/__rpc_controlstrip?user=brad",
              onData: function (data) {
                  var body = document.getElementsByTagName("body")[0];
                  var div = document.createElement("div");
                  div.innerHTML = data;
                      body.appendChild(div);
              },
              onError: function (msg) { }
            });
        }
    }
    DOM.addEventListener(window, "load", controlstrip_init);
</script>
    <link rel="meta" type="application/rdf+xml" title="FOAF" href="http://brad.livejournal.com/data/foaf" />
<meta name="foaf:maker" content="foaf:mbox_sha1sum '4caa1d6f6203d21705a00a7aca86203e82a9cf7a'" />
<link rel="group friends made" title="LiveJournal friends" href="http://brad.livejournal.com/friends" />
<meta name="ICBM" content="37.7788,-122.3974" />

        <script language="JavaScript" type="text/javascript">
            var Site;
            if (!Site)
                Site = {};

            var site_p = {"media_embed_enabled": 1,
"inbox_update_poll": 0,
"has_remote": 1,
"statprefix": "http://stat.livejournal.com",
"ctx_popup": 1,
"imgprefix": "http://stat.livejournal.com/img",
"esn_async": 1,
"currentJournal": "brad",
"siteroot": "http://www.livejournal.com",
"currentJournalBase": "http://brad.livejournal.com"};
            var site_k = ["media_embed_enabled", "inbox_update_poll", "has_remote", "statprefix", "ctx_popup", "imgprefix", "esn_async", "currentJournal", "siteroot", "currentJournalBase"];
            for (var i = 0; i < site_k.length; i++) {
                Site[site_k[i]] = site_p[site_k[i]];
            }
       </script>
    <script type="text/javascript" src="http://www.livejournal.com/js/??core.js,dom.js,httpreq.js,livejournal.js,common/AdEngine.js,esn.js,ippu.js,lj_ippu.js,hourglass.js,contextualhover.js,md5.js,login.js,livejournal-local.js?v=1186096206"></script>
<link rel="stylesheet" type="text/css" href="http://stat.livejournal.com/??lj_base.css,esn.css,contextualhover.css,controlstrip.css,controlstrip-dark.css,controlstrip-dark-local.css,controlstrip-local.css?v=1186000719" />
<style type="text/css">
/* Cleaned CSS: */
body, td {
font-family: "Verdana", sans-serif;
font-size: 10pt;
}
a {
text-decoration: none;
}
a:hover {
text-decoration: underline;
}
.shadowed {
font-size: 8pt;
background: #aaaaaa;
}
.meta {
font-size: 8pt;
}
.index {
font-size: 8pt;
}
.caption, .index {
color: #ffffff;
}
.comments {
font-size: 8pt;
}
.quickreply {
margin-top: 1em;
width:100%;
}
.box, .entrybox {
border:  hidden #000000;
}

</style>
<title>brad&#39;s life</title>
</head>
<body bgcolor="#2d4f89" text="#000000" link="#0000ff" vlink="#0000ff" alink="#00ffff">
<!-- body omitted -->
</body>
</html>
EOT
                )
            )
        );
        $this->assertEquals(
            $credential->getServer()->toString(),
            'http://www.livejournal.com/openid/server.bml'
        );

        try {
            $credential = OpenIdCredentials::create(
                HttpUrl::create()->parse('http://www.example.com/'),
                HttpClientStub::create(
                    HttpResponseStub::create()->
                    setStatus(new HttpStatus(HttpStatus::CODE_404))
                )
            );
            $this->fail();
        } catch (OpenIdException $e) {
            /* pass */
        } catch (Exception $e) {
            $this->fail();
        }
    }
}

class HttpResponseStub implements HttpResponse
{
    private $status = null;
    private $body = null;


    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus(HttpStatus $status)
    {
        $this->status = $status;
        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    public function getReasonPhrase()
    {
        throw new UnsupportedMethodException();
    }

    public function getHeaders()
    {
        throw new UnsupportedMethodException();
    }

    public function hasHeader($name)
    {
        return false;
    }

    public function getHeader($name)
    {
        return 'text/html';
    }
}

class HttpClientStub implements HttpClient
{
    private $response = null;

    public function __construct(HttpResponse $response)
    {
        $this->response = $response;
    }

    public function setTimeout($timeout)
    {
        throw new UnsupportedMethodException();
    }

    public function getTimeout()
    {
        throw new UnsupportedMethodException();
    }

    public function setFollowLocation(/* boolean */
        $really)
    {
        throw new UnsupportedMethodException();
    }

    public function isFollowLocation()
    {
        throw new UnsupportedMethodException();
    }

    public function setMaxRedirects($maxRedirects)
    {
        throw new UnsupportedMethodException();
    }

    public function getMaxRedirects()
    {
        throw new UnsupportedMethodException();
    }

    public function send(HttpRequest $request)
    {
        return $this->response;
    }

    public function setOption($key, $value)
    {
        throw new UnsupportedMethodException();
    }

    public function dropOption($key)
    {
        throw new UnsupportedMethodException();
    }

    public function getOption($key)
    {
        throw new UnsupportedMethodException();
    }

    public function setNoBody($really)
    {
        throw new UnsupportedMethodException();
    }

    public function hasNoBody()
    {
        throw new UnsupportedMethodException();
    }

    public function setMaxFileSize($maxFileSize)
    {
        throw new UnsupportedMethodException();
    }

    public function getMaxFileSize()
    {
        throw new UnsupportedMethodException();
    }
}

?>