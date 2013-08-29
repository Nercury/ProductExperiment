Overview
========

Migrate objects to different versions based on version annotations.

For example, let's say we have two class versions, the second one modifies public field name from "slug" to "id":

    ```ruby
    class V1
    {
        public $slug;
    }

    class V2
    {
        public $id;
    }
    ```