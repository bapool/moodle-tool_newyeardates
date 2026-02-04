# New Year Dates

## Brief Description
A Moodle admin tool that allows teachers to quickly update all activity and assignment dates when rolling courses forward to a new academic year. Configurable for any school calendar worldwide.

## Full Description
This plugin helps teachers efficiently roll their courses forward to a new academic year by automatically updating all date-dependent activities and assignments. The plugin is highly configurable with term-based settings that work for any school calendar system worldwide. It intelligently detects whether you're moving a course to the same term in a new year (Term 1 to Term 1) or converting between terms (Term 1 to Term 2), and automatically adjusts dates accordingly. Teachers can preview all changes before applying them, ensuring accuracy and control over the date update process.

## Features

- **Configurable Term Settings**: Define your Term 1 and Term 2 start months to match your school's academic calendar
- **Smart Semester Detection**: Automatically detects whether you're rolling forward to the same term or converting between terms
- **Automatic Date Conversion**: 
  - Same term to same term: Only years change, months stay the same
  - Term 1 to Term 2 or vice versa: Months automatically shift appropriately
- **Preview Before Applying**: Review all proposed changes in chronological order before committing
- **Universal Compatibility**: Works with any academic calendar system worldwide
- **Updates Course Start Date**: Automatically updates the course start date along with all activities
- **Accessible Interface**: Available from the course "More" menu for easy access

## Supported Activities

The plugin updates dates for the following activity types:
- **Assignments**: allowsubmissionsfromdate, duedate, cutoffdate, gradingduedate
- **Quizzes**: timeopen, timeclose
- **Forums**: duedate, cutoffdate
- **Lessons**: available, deadline
- **Choices**: timeopen, timeclose
- **Feedback**: timeopen, timeclose
- **Workshops**: submissionstart, submissionend, assessmentstart, assessmentend
- **Databases**: timeavailablefrom, timeavailableto, timeviewfrom, timeviewto
- **Glossaries**: assesstimestart, assesstimefinish
- **SCORM**: timeopen, timeclose
- **And other activities with standard date fields**: timeopen, timeclose, duedate, cutoffdate

## Installation

1. Download the plugin
2. Extract and place the `newyeardates` folder in `/admin/tool/`
3. Visit Site Administration > Notifications to complete the installation
4. Configure your term months in Site Administration > Plugins > Admin tools > New Year Dates
5. Purge all caches

## Configuration

Navigate to **Site Administration > Plugins > Admin tools > New Year Dates** to configure:

**Required Settings:**
- **Term 1 start month**: The month when your first term/semester begins (default: August)
- **Term 2 start month**: The month when your second term/semester begins (default: January)

### Configuration Examples

**Example 1: US School (Northern Hemisphere)**
- Term 1 start month: August (Fall semester)
- Term 2 start month: January (Spring semester)

**Example 2: Australian School (Southern Hemisphere)**
- Term 1 start month: February (Autumn term)
- Term 2 start month: July (Winter term)

**Example 3: UK School**
- Term 1 start month: September (Autumn term)
- Term 2 start month: January (Spring term)

## Usage

1. Navigate to any course
2. Click **"More"** in the course navigation menu
3. Select **"Update course dates for new year"**
4. Set the new course start date using the date picker
5. Click **"Set Activity Dates"** to preview all proposed changes
6. Review the changes summary (displayed in chronological order)
7. Click **"Confirm and apply these changes"** to update all dates
8. The course start date and all activity dates will be updated

## How It Works

The plugin uses intelligent date logic based on your configured term months:

### Same Term to Same Term
When the old and new start dates are in the same term (e.g., August 2025 to August 2026):
- Only the years change
- Months and days remain the same
- Two-year spans are preserved (e.g., Aug-Dec in year 1, Jan-May in year 2)

### Term Conversion
When converting between terms (e.g., August to January or January to August):
- Months are automatically shifted based on the offset between term start months
- Example: If Term 1 = August and Term 2 = January (5 month difference):
  - Term 1 to Term 2: August→January, September→February, October→March, etc.
  - Term 2 to Term 1: January→August, February→September, March→October, etc.

### Examples

**Example 1: Fall to Fall (Same Term)**
- Configuration: Term 1 = August, Term 2 = January
- Old course start: August 18, 2025
- New course start: August 18, 2026
- Result: All dates move forward one year, months stay the same
  - Assignment due September 15, 2025 → September 15, 2026
  - Quiz due January 20, 2026 → January 20, 2027

**Example 2: Fall to Spring (Term Conversion)**
- Configuration: Term 1 = August, Term 2 = January
- Old course start: August 18, 2025
- New course start: January 13, 2026
- Result: Months shift by 5 months forward
  - Assignment due September 15, 2025 → February 15, 2026
  - Quiz due November 10, 2025 → April 10, 2026

**Example 3: Spring to Fall (Term Conversion)**
- Configuration: Term 1 = August, Term 2 = January
- Old course start: January 13, 2026
- New course start: August 18, 2026
- Result: Months shift by 7 months forward
  - Assignment due February 15, 2026 → September 15, 2026
  - Quiz due April 10, 2026 → November 10, 2026

## Use Cases

- **Annual Course Rollover**: Quickly prepare courses for the next academic year
- **Semester Conversion**: Convert fall courses to spring or vice versa
- **Template Reuse**: Use a course from one term as a template for another
- **Multi-Year Planning**: Prepare courses multiple years in advance

## Requirements

- Moodle 4.5 or higher
- PHP 7.4 or higher

## Privacy

This plugin does not store any personal data. It only modifies course activity dates based on teacher actions. No student data is collected or processed.

## Permissions

The plugin requires the `moodle/course:update` capability, which is assigned by default to:
- Editing teachers
- Managers

## License

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Moodle. If not, see <http://www.gnu.org/licenses/>.

## Author

Brian Pool, 2026

## Support

For issues, questions, or contributions, please visit the plugin's repository or contact the author.

## Changelog

See CHANGES.md for version history.
