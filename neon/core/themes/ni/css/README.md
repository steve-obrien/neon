Problems with current neon templating and smarty


- No ability to easily create layout components: A layout component is concerned only with layout.  It has no idea
  about the contents of what it is laying out beyond the children.  Likewise the children are not aware they are inside a layout component.
  The layout component can attach details to the children in order to confirm with its layout such as width and spacing rules.
  
- Arbitrary Html tags can not be easily modified.