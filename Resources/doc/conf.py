# -*- coding: utf-8 -*-

import sys, os
from sphinx.highlighting import lexers
from pygments.lexers.web import PhpLexer

lexers['php'] = PhpLexer(startinline=True)

extensions = []

source_suffix = '.rst'
source_encoding = 'utf-8'
master_doc = 'index'

project = u'ZanuiFixturesBundle'
copyright = u'2014, Internet Services Australia 3 Pty Limited (http://www.zanui.com.au)'

# The short X.Y version.
version = '1.0'
# The full version, including alpha/beta/rc tags.
release = '1.0.1'

html_theme = 'default'
htmlhelp_basename = 'zanui-fixtures-bundle'
