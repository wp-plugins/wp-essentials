jQuery(document).ready(function($){tinymce.create('tinymce.plugins.video',{init:function(ed,url){ed.addCommand('video',function(){videoSelected=tinyMCE.activeEditor.selection.getContent();if(videoSelected){videoContent='[video]'+videoSelected+'[/video]';}else{var videoAnswer=prompt("Insert Video URL");if(videoAnswer){videoContent='[video]'+videoAnswer+'[/video]';}}tinymce.execCommand('mceInsertContent',false,videoContent);delete window.videoSelected;delete window.videoContent;delete window.videoAnswer;});ed.addButton('video',{title:'Insert Video',cmd:'video',image:path_url+"/images/video-button.png"});}});tinymce.PluginManager.add('video',tinymce.plugins.video);});