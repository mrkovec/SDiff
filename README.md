# SDiff
Simple and stupid text diff.

Basic usage:
```php
$a = "marry had lambs";
$b = "mary had a little lamb";
```
after
```php
$result = SDiff::getCharDiff($a, $b);
```
`$result["diff"]` will contains
```html
mar<del>r</del>y had <ins>a</ins><ins> </ins>l<ins>i</ins><ins>t</ins><ins>t</ins><ins>l</ins><ins>e</ins><ins> </ins><ins>l</ins>amb<del>s</del>
```
and
```php
$result = SDiff::getWordDiff($a, $b);
```
result in
```html
mar<del>r</del>y had <ins>a</ins> <ins>little</ins> lamb<del>s</del>
```
For object diff:
```php
$a = [
  'marry' => 'had lambs'
];
$b = [
  'mary' => 'had a little lamb'
];
$result = SDiff::getObjectDiff($a, $b);
```
`$result` will contain
```html
{
    mar<del>r</del>y: had <ins>a</ins> <ins>little</ins> lamb<del>s</del>
}
```
 
