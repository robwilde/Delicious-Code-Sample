<?php

	define( 'TEMPLATES', plugin_dir_path( __FILE__ ) . 'templates/' );

	require_once( 'class-db.php' );

	/**
	 * Created by WTC.
	 * User: Rob Wilde
	 * Date: 16/03/2015
	 * Time: 1:34 PM
	 */
	class KML_Creator
	{

		public $zone_name;
		public $styleID;
		public $lineColor;
		public $fillColor;

		public $postcodes;
		public $kml_node;
		public $polygon_node;
		public $style_node;

		private $kml;
		private $style;
		private $placemark;
		private $polygon;
		private $before;

		private $gzone_db;

//		private $poa_query;

		public function __construct( $form_feilds )
		{

			$base_xml = file_get_contents( TEMPLATES . 'base.xml' );
			$style_xml = file_get_contents( TEMPLATES . 'style.xml' );
			$placemark_xml = file_get_contents( TEMPLATES . 'placemark.xml' );
			$multigeomtry_xml = file_get_contents( TEMPLATES . 'multigeomtry.xml' );
			$polygon_xml = file_get_contents( TEMPLATES . 'polygon.xml' );

			$this->postcodes = explode( ' ', $form_feilds[ 'post_codes' ] );
			$this->zone_name = $form_feilds [ 'zone_name' ];
			$this->lineColor = $form_feilds[ 'line_color' ];
			$this->fillColor = $form_feilds[ 'fill_color' ];

			$this->kml = new SimpleXMLElement( $base_xml );
			$this->style = new SimpleXMLElement( $style_xml );
			$this->placemark = new SimpleXMLElement( $placemark_xml );
			$this->multigeomtry = new SimpleXMLElement( $multigeomtry_xml );
			$this->polygon = new SimpleXMLElement( $polygon_xml );

			$this->before = FALSE;
		}

		/**
		 * Return multi-dimensional array with postcode and coordinates
		 * @return array
		 */
		public function getCoordinates()
		{
			$poa_coord = array();
			$poa_db = new DB();

			if ( is_array( $this->postcodes ) )
			{
				foreach ( $this->postcodes as $postcode )
				{
					$poa_db->bind( "postcode", $postcode );
					$poa = $poa_db->query( "SELECT * FROM poa_coord WHERE postCode = :postcode" );

					if ( $poa[ 0 ][ 'multigeo' ] == 1 )
					{
						$poa_db->bind( "poa_cord_id", $poa[ 0 ][ 'poa_ID' ] );
						$multigeo = $poa_db->query( "SELECT * FROM multigeo_coord WHERE poa_cord_id = :poa_cord_id" );
						foreach ( $multigeo as $multi )
						{
							$poa_coord[ $postcode ][ ] = $multi[ 'multigeo_cord' ];
						}
					}
					else
					{
						$poa_coord[ $postcode ] = $poa[ 0 ][ 'coordinates' ];
					}
				}
			}

			return $poa_coord;
		}

		/**
		 * Insert XML into a SimpleXMLElement
		 *
		 * @param SimpleXMLElement $parent
		 * @param string $xml
		 * @param bool $before
		 *
		 * @return bool XML string added
		 */
		function simplexml_import_xml( SimpleXMLElement $parent, $xml, $before = FALSE )
		{
			$xml = (string)$xml;

			// check if there is something to add
			if ( $nodata = !strlen( $xml ) or $parent[ 0 ] == NULL )
			{
				return $nodata;
			}

			// add the XML
			$node = dom_import_simplexml( $parent );
			$fragment = $node->ownerDocument->createDocumentFragment();
			$fragment->appendXML( $xml );

			if ( $before )
			{
				return (bool)$node->parentNode->insertBefore( $fragment, $node );
			}

			return (bool)$node->appendChild( $fragment );
		}

		/**
		 * Insert SimpleXMLElement into SimpleXMLElement
		 *
		 * @param SimpleXMLElement $parent
		 * @param SimpleXMLElement $child
		 * @param bool $before
		 *
		 * @return bool SimpleXMLElement added
		 */
		function simplexml_import_simplexml( SimpleXMLElement $parent, SimpleXMLElement $child, $before = FALSE )
		{
			// check if there is something to add
			if ( $child[ 0 ] == NULL )
			{
				return TRUE;
			}

			// if it is a list of SimpleXMLElements default to the first one
			$child = $child[ 0 ];

			// insert attribute
			if ( $child->xpath( '.' ) != array( $child ) )
			{
				$parent[ $child->getName() ] = (string)$child;

				return TRUE;
			}

			$xml = $child->asXML();

			// remove the XML declaration on document elements
			if ( $child->xpath( '/*' ) == array( $child ) )
			{
				$pos = strpos( $xml, "\n" );
				$xml = substr( $xml, $pos + 1 );
			}

			return $this->simplexml_import_xml( $parent, $xml, $before );
		}

		/**
		 * @return array
		 */
		public function createPolygon ( $getCoordinates, $styleID )
		{
			$placemark = clone $this->placemark;
			$multigeomtry = clone $this->multigeomtry;
			foreach ( $getCoordinates as $postCode => $coordinates )
			{
				if ( is_array ( $coordinates ) )
				{
					// has multiple polygons for the postcode will need multigeomtry
					foreach ( $coordinates as $coordinate )
					{
						$this->polygon->outerBoundaryIs->LinearRing->coordinates = $coordinate;									// push the coordinates into polygon
						$this->simplexml_import_simplexml ( $multigeomtry, $this->polygon );													// push polygon to end of multigeomtry
					}

					$placemark->name     = $postCode;																														// adding the postcode to the place mark
					$placemark->styleUrl = '#' . $styleID;																													// adding the fill and line style name

					$this->simplexml_import_simplexml ( $placemark, $multigeomtry );																// push completed multi into placemark
					$multigeomtry = clone $this->multigeomtry;																									// clone multi for next pass

					$this->simplexml_import_simplexml ( $this->kml->Document, $placemark );											// add placemark to document section
					$placemark = clone $this->placemark;																												// clone clean placemark for next pass
				}
				else
				{
					$this->polygon->outerBoundaryIs->LinearRing->coordinates = $coordinates;
					$this->simplexml_import_simplexml ( $placemark, $this->polygon );

					$placemark->name     = $postCode;
					$placemark->styleUrl = '#' . $styleID;

					$this->simplexml_import_simplexml ( $this->kml->Document, $placemark );
					$placemark = clone $this->placemark;
				}
			}
		}

		/**
		 * Add the Polygons into primary KML
		 */
		public function buildKML()
		{
			$getCoordinates = $this->getCoordinates();
			$this->styleID = preg_replace( '/\s+/', '', $this->zone_name );

			$this->style[ 'id' ] = $this->styleID;
			$this->style->LineStyle->color = $this->lineColor;
			$this->style->PolyStyle->color = $this->fillColor;

			$this->kml->Document->name = $this->zone_name;
			$this->simplexml_import_simplexml( $this->kml->Document, $this->style );

			$this->createPolygon( $getCoordinates, $this->styleID );

			return $this->kml;
		}
	}
