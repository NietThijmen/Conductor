<?php

use App\Mcp\Servers\ChangelogServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp', ChangelogServer::class);
