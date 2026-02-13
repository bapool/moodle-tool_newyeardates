# Changelog

All notable changes to the New Year Dates plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [1.1.0] - 2026-02-13

### Major Improvements - Moodle Standards Compliance

This release focuses on bringing the plugin up to Moodle plugin submission standards and implementing modern best practices.

#### Added
- **Templates and Output API**: Complete refactoring to use Mustache templates and renderer classes
  - Created `classes/output/renderer.php` for custom rendering
  - Created `classes/output/date_selection_form.php` renderable
  - Created `classes/output/changes_preview.php` renderable
  - Created `templates/date_selection_form.mustache` template
  - Created `templates/changes_preview.mustache` template
- **Privacy API Implementation**: Added null provider to comply with GDPR requirements
  - Created `classes/privacy/provider.php`
  - Added privacy metadata language string
- **Event Triggers**: Implemented Moodle events system
  - Triggers `\core\event\course_updated` when course start date changes
  - Triggers `\core\event\course_module_updated` for each activity updated
  - Allows other plugins and systems to respond to date changes

#### Fixed
- **Namespace Collisions**: All functions now use proper frankenstyle naming
  - Renamed all functions to `tool_newyeardates_*` prefix
  - Moved functions from index.php to lib.php for better organization
- **UI Issue with Empty Dates**: Improved handling of invalid/empty date values
  - Added validation to skip dates before year 2000
  - Added "Not set" display for invalid dates instead of showing epoch dates
  - Enhanced date validation in `get_activities_with_dates()` function

#### Changed
- Refactored index.php to use templates (reduced from 128 to 101 lines)
- Removed `tool_newyeardates_display_changes_table()` function (replaced by template)
- Updated all HTML generation to use Output API instead of direct echo/html_writer
- Improved code maintainability and separation of concerns

#### Technical Details
- Version bumped to 1.1.0 (2026021301)
- Now fully compliant with Moodle plugin contribution guidelines
- Follows modern Moodle 4.x best practices
- Better theme support through template system

## [1.0.0] - 2026-02-04

### Initial Release

First stable release of the New Year Dates plugin for Moodle.

#### Features
- Configurable term-based date system (Term 1 and Term 2 start months)
- Smart detection of same-term vs. cross-term conversions
- Automatic month shifting for term conversions
- Preview changes before applying with chronological sorting
- Update course start date along with all activity dates
- Support for multiple activity types (assignments, quizzes, forums, lessons, workshops, etc.)
- Integration with course "More" menu for easy access
- Capability-based access control (requires moodle/course:update)
- Privacy-compliant (no personal data storage)
- Comprehensive language strings
- User-friendly interface with confirmation step
- Works with any academic calendar system worldwide

#### Supported Moodle Versions
- Moodle 4.5 or higher

#### Requirements
- PHP 7.4 or higher
