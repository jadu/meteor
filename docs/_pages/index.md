---
permalink: /
layout: default
---
# What is Meteor?

Meteor is a packaging and deployment tool for the Jadu Continuum platform.

### Guides

{% assign guides = site.guides | sort: 'title' %}

<ul>
  {% for guide in guides %}
    <li><a href="{{ site.baseurl }}{{ guide.url }}">{{ guide.title }}</a></li>
  {% endfor %}
</ul>

### Documentation

{% assign docs = site.docs | sort: 'title' %}

<ul>
  {% for doc in docs %}
    <li><a href="{{ site.baseurl }}{{ doc.url }}">{{ doc.title }}</a></li>
  {% endfor %}
</ul>
