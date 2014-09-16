<html>
    <head>
        <title>default_crate</title>
    </head>
    <body>
        <article>
            <h1>"{{ crate_name }}" Data Package README file</h1>
            <section resource="creative work" typeof="http://schema.org/CreativeWork">
                  <h1>Package Title</h1>
                  <span property="http://schema.org/name http://purl.org/dc/elements/1.1/title">{{ crate_name }}</span>
                  <h1>Package Creation Date</h1>
                  <span content="{{ created_date }}" property="http://schema.org/dateCreated">{{ created_date_formatted }}</span>
                  <h1>Package File Name</h1>
                  <span property="http://schema.org/name">{{ crate_name }}.zip</span>
                  <h1>ID</h1>
                  <span property="http://schema.org/id">{{ crate_name }}</span>
                  <h1>Description</h1>
                  <span property="http://schema.org/description">{{ description | nl2br }}</span>

                      <h1>Creators</h1>
                      {% if creators %}
                        <table border="1">
                            <thead>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Identifier</th>
                                <th>Source</th>                          
                            </thead>
                            <tbody>
                                {% for creator in creators %}  
                                    <tr>
                                        {% if creator.overrides.name %}
                                            <td>{{ creator.overrides.name }}</td>  
                                        {% else %}
                                            <td>{{ creator.name }}</td>
                                        {% endif %}
                                        
                                        {% if creator.overrides.email %}
                                            <td>{{ creator.overrides.email }}</td>  
                                        {% else %}
                                            <td>{{ creator.email }}</td>   
                                        {% endif %}
                                        <td xmlns:dc="http://purl.org/dc/elements/1.1/">
                                            {% if creator.url %}
                                                {% if not creator.overrides.identifier is empty %}
                                                    <a href="{{ creator.overrides.identifier }}">
                                                        <span property="dc:identifier">{{ creator.overrides.identifier }}</span>
                                                    </a>
                                                {% else %}
                                                    <a href="{{ creator.identifier }}">
                                                        <span property="dc:identifier">{{ creator.overrides.identifier }}</span>
                                                    </a>
                                                {% endif %}
                                            {% else %}
                                                <span property="dc:identifier">{{ creator.overrides.identifier }}</span>
                                            {% endif %}
                                        </td>
                                        <td>{{ creator.source}}</td>
                                    </tr>
                                {% endfor %}
                            </tbody>
                        </table>
                     {% else %}
                     <span>None.</span>
                     {% endif %}
                     
                      <h1>Grants</h1>
                      
                      {% if activities %}
                        <table border="1">
                            <thead>
                                <th>Grant Number</th>
                                <th>Grant Title</th>                              
                                <th>Description</th>
                                <th>Date Granted</th>
                                <th>Date Submitted</th>
                                <th>Institution</th>   
                                <th>Identifier</th>
                                <th>Source</th>     
                                <th>Subject</th>
                                <th>Format</th>
                                <th>OAI Set</th>
                                <th>Repository Name</th>
                                <th>Repository Type</th>
                                <th>Display Type</th>
                                <th>Contributors</th>
                                                        
                            </thead>
                            <tbody>
                                {% for activity in activities %}  
                                    <tr>
                                        {% if activity.overrides.grant_number %}
                                            <td>{{ activity.overrides.grant_number }}</td>  
                                        {% else %}
                                            <td>{{ activity.grant_number }}</td>
                                        {% endif %}
                                        
                                        {% if activity.overrides.title %}
                                            <td>{{ activity.overrides.title }}</td>  
                                        {% else %}
                                            <td>{{ activity.title }}</td>
                                        {% endif %}
                                        
                                        <td>{{activity.description}}</td>
                                        
                                        {% if activity.overrides.date %}
                                            <td>{{ activity.overrides.date }}</td>
                                        {% else %}
                                            <td>{{ activity.date }}</td>
                                        {% endif %}  
                                        
                                        <td>{{ activity.date_submitted}}</td>
                                                                          
                                        {% if activity.overrides.institution %}
                                            <td>{{ activity.overrides.institution }}</td>  
                                        {% else %}
                                            <td>{{ activity.institution }}</td>
                                        {% endif %}
                                        
                                        {% if activity.identifier[:5] == 'http:' or activity.identifier[:6] == 'https:' %}
                                            <td><a href="{{activity.identifier}}">{{activity.identifier}}</a></td>
                                        {% else %}
                                            <td>{{ activity.identifier }} </td>
                                        {% endif %}
                                        <td>{{ activity.source }}</td>
                                        
                                        <td>{{ activity.subject }}</td>
                                        
                                        <td>{{ activity.format }}</td>
                                        
                                        <td>{{ activity.oai_set }}</td>
                                        
                                        <td>{{ activity.repository_name }}</td>
                                        
                                        <td>{{ activity.repository_type }}</td>
                                        
                                        <td>{{ activity.display_type }}</td>
                                        
                                        <td>{{ activity.contributors }}</td>
                                    </tr>
                                {% endfor %}
                            </tbody>
                        </table>  
                    {% else %}
                     <span>None.</span>   
                    {% endif %}        
                  <h1>Software Information</h1>
                  <section property="http://purl.org/dc/terms/creator" typeof="http://schema.org/softwareApplication" resource="">
                    <table border="1">
                        <tbody>
                            <tr>
                                <td>Generating Software Application</td>
                                <td property="http://schema.org/name">Cr8it</td>
                            </tr>
                            <tr>
                                <td>Software Version</td>
                                <td property="http://schema.org/softwareVersion">{{ version }}</td>
                            </tr>
                            <tr>
                                <td>URLs</td>
                                <td>
                                    <li><a href="https://github.com/IntersectAustralia/owncloud" property="http://schema.org/url">
                                                https://github.com/IntersectAustralia/owncloud</a></li>
                                    <li><a href="https://github.com/uws-eresearch/apps" property="http://schema.org/url">
                                                https://github.com/uws-eresearch/apps</a></li>
                                    <li><a href="http://eresearch.uws.edu.au/blog/projects/projectsresearch-data-repository/" property="http://schema.org/url">
                                                http://eresearch.uws.edu.au/blog/projects/projectsresearch-data-repository</a></li>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                  </section>
               </section>

                  <h1>Files</h1>                    
                  {% if files %}
                     {{ filetree | raw }}
                  {% else %}
                  <span>None.</span>
                  {% endif %}
        </article>
    </body>
</html>