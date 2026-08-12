<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LegalPage;

class LegalPagesSeeder extends Seeder
{
    public function run(): void
    {
        LegalPage::updateOrCreate(
            ['key' => 'terms'],
            [
                'content' => '<div class="wrap">

  <header class="page-head">
    <h1>Terms of Use</h1>
    <p>Medical Glasses Donation Platform</p>
  </header>

  <div class="box">
    <p>
      By using this platform (whether as a donor, a recipient, or a visitor), you agree to be bound by the
      following terms and conditions. If you do not agree with any part of these terms, please discontinue
      use of the platform.
    </p>
  </div>

  <h2>1. Nature of the Service</h2>
  <p>
    The platform is a technical intermediary that allows donors to list used or new prescription glasses,
    and allows recipients to browse these listings and contact donors to receive them. The platform does not
    buy or sell glasses, and does not guarantee the medical or physical condition of any listed glasses; it
    only facilitates communication and coordination between the two parties.
  </p>

  <h2>2. Registration and Account Requirements</h2>
  <ul>
    <li>Information provided when creating an account must be accurate and truthful.</li>
    <li>Registration requires phone number verification via a confirmation code before certain features are enabled.</li>
    <li>You are responsible for keeping your login credentials confidential, and for any activity carried out through your account.</li>
    <li>The platform reserves the right to suspend or terminate any account that violates these terms or is used abusively.</li>
  </ul>

  <h2>3. Donor Obligations</h2>
  <ul>
    <li>Accurately describe the glasses being listed (condition, frame size, and prescription details if applicable).</li>
    <li>Do not list glasses with false or misleading descriptions.</li>
    <li>Act in good faith when handling contact requests, and respond within a reasonable time.</li>
    <li>Correctly update the status of a listing (marked as donated, no longer available) once delivery is completed.</li>
  </ul>

  <h2>4. Recipient Obligations</h2>
  <ul>
    <li>Only submit genuine, serious contact requests, and refrain from misusing the request system (e.g., repeated non-serious requests).</li>
    <li>Honestly confirm receipt of glasses — or the lack thereof — through the platform,s "delivery confirmation" feature as soon as possible.</li>
    <li>Do not request any monetary payment from the donor or the platform in exchange for this service, as it is a non-profit donation service.</li>
  </ul>

  <h2>5. Communication and Chat</h2>
  <p>
    The platform provides an internal chat system to facilitate coordination between a donor and a recipient
    after a contact request is accepted. This system may not be used for any purpose that is unlawful, abusive,
    or unrelated promotional activity outside the scope of the donation. The platform reserves the right to
    close or suspend any conversation in cases of suspected misuse, or based on a verified complaint.
  </p>

  <h2>6. Complaints and Misuse</h2>
  <p>
    If any party (donor or recipient) engages in inappropriate or non-compliant behavior, a complaint may be
    submitted through the dedicated complaints system. The administration team reserves the right to review the
    complaint, communicate with the relevant parties, and take appropriate action, including suspending or
    terminating an account in serious cases.
  </p>

  <h2>7. Disclaimer</h2>
  <ul>
    <li>The platform is not responsible for the actual condition of donated glasses, or how accurately it matches the donor,s description.</li>
    <li>The platform is not responsible for any arrangement made outside the system between a donor and a recipient.</li>
    <li>No medical advice is provided through the platform; prescription details shown are informational only, as entered by the
        user, and any decision regarding the suitability of a pair of glasses for your vision should be made in
        consultation with an eye care professional.</li>
  </ul>

  <h2>8. Intellectual Property</h2>
  <p>
    All elements of the platform (design, logo, static content, and code) are owned by the platform and may not
    be copied or reused commercially without prior written permission. Content uploaded by users (such as photos
    of glasses) remains their property, while granting the platform a non-exclusive license to display it solely
    within the service.
  </p>

  <h2>9. Changes to These Terms</h2>
  <p>
    The platform reserves the right to modify these terms at any time. Users will be notified of any material
    changes, and continued use of the platform after such changes constitutes implicit acceptance of the updated
    terms.
  </p>

  <h2>10. Governing Law</h2>
  <p>
    These terms shall be governed by and interpreted in accordance with the laws of [Insert jurisdiction here],
    and any dispute arising from them shall be referred to the competent courts of that jurisdiction.
  </p>

  <div class="note">
    ⚠️ Note: This is a general, customizable template and does not constitute legal advice. We recommend having
    it reviewed by a qualified legal professional before publishing it officially, to ensure compliance with
    local regulations governing donations and non-profit services in your jurisdiction.
  </div>

  <footer>
    All rights reserved © GiveSight — 2026
  </footer>

</div>

',
                'published_at' => now(),
            ]
        );

        LegalPage::updateOrCreate(
            ['key' => 'privacy'],
            [
                'content' => '<div class="wrap">

  <header class="page-head">
    <h1>Privacy Policy</h1>
    <p>Last updated: [Insert date] &nbsp;|&nbsp; Medical Glasses Donation Platform</p>
  </header>

  <div class="box">
    <p>
      We are committed to protecting the privacy of everyone who uses our platform, whether you are a
      "Donor" offering to donate prescription glasses, or a "Recipient" looking to receive a pair.
      This policy explains what data we collect, how we use it, and your rights regarding it.
    </p>
  </div>

  <h2>1. Information We Collect</h2>
  <ul>
    <li>Account information: name, email address, and phone number (verified via a confirmation code).</li>
    <li>Glasses listing details: description, condition, frame size, prescription details (if provided), and attached photos.</li>
    <li>Communication data: messages exchanged between donors and recipients through the platform,s internal chat system.</li>
    <li>Transaction data: the status of each donation (pending, accepted, delivered, confirmed received), and issued donation receipts.</li>
    <li>Technical data: IP address, browser type, and usage logs, used for security purposes and to prevent abuse.</li>
  </ul>

  <h2>2. How We Use Your Information</h2>
  <ul>
    <li>To operate the donation process and connect donors with suitable recipients.</li>
    <li>To send notifications related to your requests (new contact request, acceptance, rejection, delivery confirmation, receipt issuance).</li>
    <li>To verify user identity via phone number and reduce fraudulent accounts.</li>
    <li>To review complaints and resolve disputes between parties when necessary.</li>
    <li>To improve the platform and analyze overall usage (using aggregated, de-identified data wherever possible).</li>
  </ul>

  <h2>3. Sharing Information With Others</h2>
  <p>
    Direct contact information (such as phone numbers) is not shared between a donor and a recipient until
    a contact request has been accepted by both parties, and only within the limits each user sets under their
    listing,s "contact method" preference. We do not sell your data to any marketing party, and we do not share
    it with third parties except in the following cases:
  </p>
  <ul>
    <li>When required by a legal obligation.</li>
    <li>To protect the rights of the platform or its users in cases of proven misuse.</li>
    <li>With technical service providers (such as server hosting or SMS verification services), under confidentiality agreements.</li>
  </ul>

  <h2>4. Data Retention and Deletion</h2>
  <p>
    We retain donation records and receipts for a reasonable period for documentation and audit purposes,
    even after an account is closed, unless a longer period is legally required. You may request the deletion
    of your account and personal data at any time, though some records tied to completed donations (such as
    receipts) may be retained in a de-identified form for statistical purposes.
  </p>

  <h2>5. Data Security</h2>
  <p>
    We take reasonable measures to protect your data from unauthorized access, including encrypted connections,
    authorization checks before displaying any sensitive data, and restricting access to administrative data to
    authorized staff only.
  </p>

  <h2>6. Your Rights</h2>
  <ul>
    <li>Access the data we store about you and request a copy of it.</li>
    <li>Correct any inaccurate information in your profile.</li>
    <li>Request deletion of your account, subject to the exceptions noted above.</li>
    <li>Withdraw your consent to promotional notifications (excluding notifications essential to operating your account).</li>
  </ul>

  <h2>7. Changes to This Policy</h2>
  <p>
    We may update this policy from time to time. You will be notified of any material changes via email or an
    in-platform notification, and the "last updated" date at the top of this page will always reflect the most
    recent revision.
  </p>

  <h2>8. Contact Us</h2>
  <p>
    For any questions regarding your privacy or data, please contact us at: [Insert support email here].
  </p>

  <div class="note">
    ⚠️ Note: This is a general, customizable template and does not constitute legal advice. We recommend having
    it reviewed by a qualified legal professional before publishing it officially, particularly regarding data
    protection obligations applicable in your jurisdiction (such as GDPR or similar local regulations).
  </div>

  <footer>
    All rights reserved © GiveSight — 2026
  </footer>

</div>
',
                'published_at' => now(),
            ]
        );
    }
}