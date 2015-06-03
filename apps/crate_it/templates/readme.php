<html>
    <head>
        <title>default_crate</title>
    </head>
    <body>
        <article>
            <h1>"<?php p($_['crate_name']) ?>" Data Package README file</h1>
            <section resource="creative work" typeof="http://schema.org/CreativeWork">
                  <h1>Package Title</h1>
                  <span property="http://schema.org/name http://purl.org/dc/elements/1.1/title"><?php p($_['crate_name']) ?></span>
                  <h1>Package Creation Date</h1>
                  <span content="<?php p($_['created_date']) ?>" property="http://schema.org/dateCreated"><?php p($_['created_date_formatted']) ?></span>
                  <h1>Package File Name</h1>
                  <span property="http://schema.org/name"><?php p($_['crate_name']) ?>.zip</span>
                  <h1>ID</h1>
                  <span property="http://schema.org/id"><?php p($_['crate_name']) ?></span>
                  <h1>Description</h1>
                  <span property="http://schema.org/description"><?php p($_['description | nl2br']) ?></span>

                      <h1>Creators</h1>
                       <?php if(isset($_['creators'])) { ?>
                        <table border="1">
                            <thead>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Identifier</th>
                                <th>Source</th>                          
                            </thead>
                            <tbody>
                                <?php foreach($_['creators'] as $creator) { ?>  
                                    <tr>
<!--                                         <php if (creator.overrides.name) { ?>
                                            <td><?php p($_['creator.overrides.name']) ?></td>  
                                        <php } else { ?>
                                            <td><?php p($_['creator.name']) ?></td>
                                        <php } ?>
                                        
                                        <php if (creator.overrides.email) { ?>
                                            <td><?php p($_['creator.overrides.email']) ?></td>  
                                        <php } else { ?>
                                            <td><?php p($_['creator.email']) ?></td>   
                                        <php } ?>
                                        <td xmlns:dc="http://purl.org/dc/elements/1.1/">
                                            <php if (creator.url) { ?>
                                                <php if (not creator.overrides.identifier is empty) { ?>
                                                    <a href="<?php p($_['creator.overrides.identifier']) ?>">
                                                        <span property="dc:identifier"><?php p($_['creator.overrides.identifier']) ?></span>
                                                    </a>
                                                <php } else { ?>
                                                    <a href="<?php p($_['creator.identifier']) ?>">
                                                        <span property="dc:identifier"><?php p($_['creator.overrides.identifier']) ?></span>
                                                    </a>
                                                <php } ?>
                                            <php } else { ?>
                                                <span property="dc:identifier"><?php p($_['creator.identifier']) ?></span>
                                            <php } ?>
                                        </td>
                                        <td><?php p($_['creator.source']) ?></td> -->
                                    </tr>
                                {% endfor %}
                            </tbody>
                        </table>
                     <php } else { ?>
                     <span>None.</span>
                     <php } ?>
                     
                      <h1>Grants</h1>
                      
                      <php if (activities) { ?>
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
                                <?php foreach($_['activities'] as $activity) { ?>  
                                    <tr>
<!--                                         <php if($activity.overrides.grant_number) { ?>
                                            <td><?php p($_['activity.overrides.grant_number']) ?></td>  
                                        <php } else { ?>
                                            <td><?php p($_['activity.grant_number']) ?></td>
                                        <php } ?>
                                        
                                        <php if (activity.overrides.title) { ?>
                                            <td><?php p($_['activity.overrides.title']) ?></td>  
                                        <php } else { ?>
                                            <td><?php p($_['activity.title']) ?></td>
                                        <php } ?>
                                        
                                        <td><?php p($_['activity.description']) ?></td>
                                        
                                        <php if (activity.overrides.date) { ?>
                                            <td><?php p($_['activity.overrides.date']) ?></td>
                                        <php } else { ?>
                                            <td><?php p($_['activity.date']) ?></td>
                                        <php } ?>  
                                        
                                        <td><?php p($_['activity.date_submitted']) ?></td>
                                                                          
                                        <php if (activity.overrides.institution) { ?>
                                            <td><?php p($_['activity.overrides.institution']) ?></td>  
                                        <php } else { ?>
                                            <td><?php p($_['activity.institution']) ?></td>
                                        <php } ?>
                                        
                                        <php if (activity.identifier[:5] == 'http:' or activity.identifier[:6] == 'https:') { ?>
                                            <td><a href="<?php p($_['activity.identifier']) ?>"><?php p($_['activity.identifier']) ?></a></td>
                                        <php } else { ?>
                                            <td><?php p($_['activity.identifier']) ?> </td>
                                        <php } ?> -->
                                        <td><?php p($_[$activity.source) ?></td>
                                        <td><?php p($_[$activity.subject) ?></td>
                                        <td><?php p($_[$activity.format) ?></td>
                                        <td><?php p($_[$activity.oai_set) ?></td>
                                        <td><?php p($_[$activity.repository_name) ?></td>
                                        <td><?php p($_[$activity.repository_type) ?></td>
                                        <td><?php p($_[$activity.display_type) ?></td>
                                        <td><?php p($_[$activity.contributors) ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>  
                    <?php } else { ?>
                     <span>None.</span>   
                    <?php } ?>
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
                                <td property="http://schema.org/softwareVersion"><?php p($_['version']) ?></td>
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
                   <?php 
                      if($_[files]){
                        print_unescaped($_['filetree']);
                      }
                      else{
                        print_unescaped('<span>None.</span>');
                      }
                  ?>
        </article>
    </body>
</html>