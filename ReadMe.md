# Quizchat Block Plugin

The Quizchat block plugin enhances the quiz experience by enabling real-time communication between instructors and students, who are currently answering the quiz.  It provides a dedicated chat window within the quiz interface, allowing instructors to provide guidance, clarification, and support to students in real-time. 

# Features

- It appears as a block in the block drawer. 
- By default only editting teachers and admins are allowed to send messages either to all quiz participants, to a specific participant, or even to themselves whenever needed. 
Students are allowed to chat only with the instructors. It also comes with built-in support for MathJax, a powerful tool for rendering mathematical equations on web pages, providing users with a rich mathematical experience within the block. 

- The plugin also allows instructors to refer a question in their messages. In this case, they have the possibility to send one message to all students who got that question and students see clickable links for the refernced question and they can directly navigate to it. 

- Students can also reference either a general or specific question number in their messages. Instructors will receive a clickable question name, enabling them to effortlessly preview the question being referenced before responding. 

- Additionally, Quizchat seamlessly manages shuffled and randomized questions, ensuring that instructors receive the exact question referenced in a clear and organized manner. This feature enhances communication efficiency, enables instructors to address student queries promptly and accurately, facilitates targeted communication and fosters a collaborative learning environment.

- In order to ensure no messages are overlooked and providing a more structured communication flow in Quizchat, instructors have the option to open the block in a new page. The Page view of Quizchat allows instructors to clearly differentiate between private and group conversations to be notified of new messages in real time, clearly see which conversation the new message belongs to, and view messages of each conversation separately for a more focused discussion experience.

- Support for Separate Groups and Groupings. Quizchat block plugin fully supports Moodle’s separate groups mode and groupings. In separate groups mode, instructors can see and send messages within their own group, maintaining privacy between groups. Additionally, messages can be filtered by the selected group or grouping, and users can send messages targeted specifically to their own group or a defined grouping. If a group or grouping is deleted, existing messages remain visible and are clearly marked as (deleted) next to the receiver’s name for clarity.

# Compatibility
This block is compatible with Moodle versions 4.3 to 5.0 and is also compatible with PHP versions 8.0 to 8.3 (please check the respective compatibility for your Moodle version). 

# Notes

- Quizchat can be added only to a quiz page. 
- Only text messages are allowed to be sent.
- Students can start chating during or after attempting a quiz. The state indicator beside each participant in participants menu states the user state in the quiz. A user-state can be one of the following: 'Attempt started','No attempt','Attempt finished' or 'Attempt abandoned'. The state indicator beside each participant in a message header can be one of the following: 'Deleted user', Unenrolled user' or 'Suspended user'; to be able to display the old messages of those users.
- Editing teachers/admins can edit the permission of the block to control which role is allowed to contact all participants (Non-editing teachers can be allowed if required).   
- Quizchat title can be modified from block configuration settings. If it is empty, it will be reset to the default value.
- Admins can modify the following variables in plugin settings page: 
   + Quizchat Poll Interval.
   + Unnotify Timeout.
- To enable MathJax functionality within this plugin, administrators need to follow a few simple steps:
   + Activate the MathJax filter: 
      * Click on "Site administration" in the left-hand menu
      * Under the "Plugins" section, click on "Filters" 
      * On the Filters page, locate the "MathJax" filter from the list of available filters. If MathJax is not listed, you may need to install the MathJax filter plugin.
      * Click the "Enable" button or select "Enable" from the dropdown menu next to it.
   + MathJax additional configuration options:
      Once enabled, you may have additional configuration options available for the MathJax filter: 
      * Set the URL for MathJax: administrator should provide the URL for the MathJax library. This URL points to the location where the MathJax JavaScript files are hosted. This step ensures that the plugin can access and utilize MathJax for rendering mathematical equations.
      * MathJax configuration: it is  is a MathJax configuration script that sets up the tex2jax and CommonHTML options. With this configuration, the MathJax filter in Moodle will recognize inline math delimiters as ```$...$``` and ```\(...\)``` and display math delimiters as ```$$...$$```. This allows the plugin to identify mathematical content within the block and render it correctly using MathJax. 
      Make sure to adjust the configuration to your specific needs if necessary.
      Click on the "Save changes" button to save your changes.
   This activation will allow Quizchat plugin to utilize MathJax for displaying mathematical content as intended.

# Installation

1. Extract the contents of the downloaded zip to `blocks` in moodle directory.
2. Start the Moodle upgrade procedure.
3. Modify Quizchat Configuration Settings if needed. 


# Permissions and Roles

| **Capability**                 | **Permission level**   | **Description**                                | **Roles**                                                   |
|:------------------------------:|:----------------------:|:----------------------------------------------:|:-----------------------------------------------------------:|
| 'block/quizchat:addinstance'   | Quiz(Module)           | Add a new instance of Quizchat block           | Manager <br> Teacher                                        |
| 'block/quizchat:sendall'       | Block                  | Send Quizchat messages to all quiz participants| Teacher                                                     |
| 'block/quizchat:sendmsg'       | Block                  | Send a new Quizchat message to instructors     | Manager <br> Teacher <br> Nonedititing Teacher <br> Student |

If permissions haven't changed, an administrator must manually change or force capabilities to be reset. To manually reset permissions to default for a role and enable role assignments, follow these steps:
- Navigate to Role Management: Go to *Administration > Site administration > Users > Permissions > Define roles*.
- Select the Role: Choose the role for which you want to reset the permissions and click on *Reset*.
- Reset Permissions: In the *Resetting role* Page, under the *Reset* section, select *Allow role assignments* and  click on *Continue*.
- Save changes: *Resetting role* Page will be redirected back. Click on *Save changes*.

To change role permissions for a Quizchat block:
- Turn editing on in the course.
- In the actions menu in the header of the block, click *Permissions*.


# Configuration Settings

- Quizchat Poll Interval (Interval for polling the webservice for new messages in seconds).
- Unnotify Timeout (Timespan in seconds between receiving a message and marking all new messages as read when the block drawer is open).
- Message length (The maximum number of characters to be sent in a single message).

# MathJax Filter Settings

Enabling MathJax Filter will allow Quizchat plugin to utilize MathJax for displaying mathematical content as intended. To enable it, administrators need to follow a few simple steps:
- Activate the MathJax filter: 
   + Click on "Site administration".
   + Under the "Plugins" section, then "Filters" subsection, click on "Manage filters".
   + On the "Manage Filters" page, locate the "MathJax" filter from the list of available filters. If MathJax is not listed, you may need to install the MathJax filter plugin.
   + Click the "Enable" button or select "Enable" from the dropdown menu next to it.
- MathJax additional configuration options:
   Once enabled, you may have additional configuration options available for the MathJax filter: 
   + Set the URL for MathJax: administrator should provide the URL for the MathJax library. This URL points to the location where the MathJax JavaScript files are hosted. This step ensures that the plugin can access and utilize MathJax for rendering mathematical equations.
   + MathJax configuration: it is a MathJax configuration script that sets up the tex2jax and CommonHTML options. With this configuration, the MathJax filter in Moodle will recognize inline math delimiters as ```$...$``` and ```\(...\)``` and display math delimiters as ```$$...$$```. This allows the plugin to identify mathematical content within the block and render it correctly using MathJax. 
- Make sure to adjust the configuration to your specific needs if necessary, then click on the "Save changes" button to save your changes.
- Example configuration:
```
MathJax.Hub.Config({
    config: ["Accessible.js", "Safe.js"],
    errorSettings: { message: ["!"] },
    skipStartupTypeset: true,
    messageStyle: "none",
    extensions: ["tex2jax.js"],
    jax: ["input/TeX", "output/HTML-CSS"],
    tex2jax: {
        inlineMath: [ ['$','$'], ["\\(","\\)"] ],
        displayMath: [ ['$$','$$'], ["\\[","\\]"] ],
        processEscapes: true
    },
    "HTML-CSS": { fonts: ["TeX"] }
});
```

# Block Settings

- Quizchat Title
### Where this block appears
- Original block location
   The original location where the block was created. For example, a quizchat block created on a quiz page could be displayed in the block drawer within that quiz.
- Display on page types
   Which page type within a quiz to display quizchat-block in. Options are: 
   + Any quiz module page --> recommended
   + Quiz information page
   + Attempt quiz page
   + Quiz attempt summary page
   + Review quiz attempt page
   + Edit quiz page
   + any quiz report page
- Default region
   Themes may define one or more named block regions where blocks are displayed. This setting defines which of these you want this block to appear in by default. The region may be overridden on specific pages if required.
- Default weight
   The default weight allows you to choose roughly where you want the block to appear in the chosen region, either at the top or the bottom. The final location is calculated from all the blocks in that region (for example, only one block can actually be at the top). This value can be overridden on specific pages if required.
### On this page
- Visible
- Region
- Weight

# How to use
   
1. Add quiz 
2. Edit quiz settings -> Appearance -> Show more ->  Show blocks during quiz attempts = yes, save and display  
3. In the block drawer, Add a block -> Quizchat
4. Modify Quizchat-Block Settings:
   - Modify Default Value for title if needed
   - Where this block appears  (default values)
     + Display on page types: should be modified. For example, if you want quizchat-block apears in any quiz module page,  or only in Quiz information page.    
   - On this page (default values)

# User states
The quizchat plugin knows these following states and indicates them using these respectives colors:
* `#d3d3d3` - User unenrolled from course, user account suspended, or User deleted 
* `#f08080` - Quiz attempt finished or Quiz attempt abandoned
* `#efef6f` - No quiz attempt by this user
* `#90ee90` - Quiz attempt in progress


# Notes for testing
When running behat tests, be aware that the scenarios with tags @uninstall_plugin_with_instance and @uninstall_plugin_no_instance will both attempt to uninstall the block_quizchat plugin. If these scenarios run successfully you are going to have to reinitialize your behat environment:
```
php admin/tool/behat/cli/util.php --drop
php admin/tool/behat/cli/init.php
```
