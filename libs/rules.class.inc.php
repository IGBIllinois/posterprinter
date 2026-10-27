<?php
//////////////////////////////////////////////////////
//
//  rules.class.inc.php
//
//  Business rules shared by the order form (html/api.php) and the admin
//  Edit Order page, so both enforce exactly the same thing.
//
//  Laminate only bonds to satin/glossy photo media. It does NOT adhere to
//  canvas or fine art (textured/absorbent) stock - a material property.
//
//  TEMPORARY: matched by database ID. IDs differ between servers and the
//  tables contain duplicate rows, so CHECK THESE AGAINST THE DATABASE ON
//  EVERY SERVER this is deployed to:
//    SELECT paperTypes_id, paperTypes_name FROM paperTypes;
//    SELECT finishOptions_id, finishOptions_name FROM finishOptions;
//  This class will be removed once DB changes are approved, replaced by
//  available finish options stored per paper type in the database.
//
//////////////////////////////////////////////////////

class rules {

	// Papers laminate does not bond to: Graphic Matte Canvas (1),
	// Fine Art Watercolor (7), Graphic Matte Canvas (Fabric) (17, 18).
	const NO_LAMINATE_PAPER_IDS = [1, 7, 17, 18];

	// Every finish row that involves laminate (plain Laminating, foam core
	// and gator board variants), available or not, as of igb-mac-box Oct 2026.
	// Listed broadly on purpose: which rows are active in production is
	// unknown, and this list is temporary.
	const LAMINATE_FINISH_IDS = [2, 3, 4, 6, 7, 9, 10, 11, 12, 14, 15, 16, 17, 18, 19, 22];

	// "None" finish rows, used to fall back when lamination gets disabled.
	const NO_FINISH_IDS = [1, 8];

	public static function paper_rejects_laminate(int $paperId): bool {
		return in_array($paperId, self::NO_LAMINATE_PAPER_IDS, true);
	}

	public static function finish_is_laminate(int $finishId): bool {
		return in_array($finishId, self::LAMINATE_FINISH_IDS, true);
	}

	public static function finish_is_none(int $finishId): bool {
		return in_array($finishId, self::NO_FINISH_IDS, true);
	}

	// Length that gets billed: if the whole poster fits across the roll,
	// it is fed short side first (bill the shorter side); otherwise the
	// short side goes across and the long side is billed.
	public static function billed_length(float $width, float $length, float $paperWidth): float {
		return (max($width, $length) <= $paperWidth) ? min($width, $length) : max($width, $length);
	}
}
